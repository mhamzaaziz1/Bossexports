<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
        
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-12">
                <button class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#stats-collapse" aria-expanded="true" aria-controls="stats-collapse">
                    <i class="fa fa-bar-chart"></i> Toggle Stats
                </button>
            </div>
        </div>

        <div class="collapse in" id="stats-collapse" aria-expanded="true">
            <?php
            // --- 1. GET URL FILTERS ---
            $f_from = $this->input->get('report_from');
            $f_to   = $this->input->get('report_to');
            $base_currency = get_base_currency();

            // --- 2. QUERY TOP STATS ---
            $this->db->select('SUM(total) as total_amount, COUNT(id) as total_rows');
            $this->db->from(db_prefix() . 'creditnotes');
            if (!empty($f_from)) $this->db->where('date >=', to_sql_date($f_from));
            if (!empty($f_to))   $this->db->where('date <=', to_sql_date($f_to));
            $stats_query = $this->db->get()->row();

            // Calculate Open (Active) vs Closed based on same filters
            $this->db->select('COUNT(id) as count');
            $this->db->from(db_prefix() . 'creditnotes');
            $this->db->where('status', 1); // 1 = Open
            if (!empty($f_from)) $this->db->where('date >=', to_sql_date($f_from));
            if (!empty($f_to))   $this->db->where('date <=', to_sql_date($f_to));
            $open_query = $this->db->get()->row();

            $stat_total_amount = $stats_query ? $stats_query->total_amount : 0;
            $stat_total_count  = $stats_query ? $stats_query->total_rows : 0;
            $stat_open_count   = $open_query ? $open_query->count : 0;

            // --- 3. QUERY GRAPH DATA (Group by Month) ---
            $this->db->select("DATE_FORMAT(date, '%Y-%m') as m, SUM(total) as total");
            $this->db->from(db_prefix() . 'creditnotes');
            if (!empty($f_from)) $this->db->where('date >=', to_sql_date($f_from));
            if (!empty($f_to))   $this->db->where('date <=', to_sql_date($f_to));
            $this->db->group_by('m');
            $this->db->order_by('date', 'ASC');
            $graph_data = $this->db->get()->result_array();

            $chart_labels = [];
            $chart_values = [];
            foreach ($graph_data as $row) {
                $chart_labels[] = $row['m'];
                $chart_values[] = $row['total'];
            }

            // --- 4. QUERY SIDE TABLE (Group by Status) ---
            $this->db->select('status, COUNT(id) as count, SUM(total) as amount');
            $this->db->from(db_prefix() . 'creditnotes');
            if (!empty($f_from)) $this->db->where('date >=', to_sql_date($f_from));
            if (!empty($f_to))   $this->db->where('date <=', to_sql_date($f_to));
            $this->db->group_by('status');
            $status_data = $this->db->get()->result_array();

            // Helper for status names
            $status_map = [
                1 => ['name' => _l('credit_note_status_open'), 'color' => '#fc2d42'], // Redish
                2 => ['name' => _l('credit_note_status_closed'), 'color' => '#84c529'], // Green
                3 => ['name' => _l('credit_note_status_void'), 'color' => '#777777'], // Grey
            ];
            ?>

            <style>
                /* Stats Styling */
                .stat-box { background: #fff; border: 1px solid #e5e5e5; padding: 25px 15px; text-align: center; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
                .stat-box h3 { margin: 0 0 5px 0; font-weight: 700; font-size: 22px; }
                .stat-box span { font-size: 11px; text-transform: uppercase; color: #777; letter-spacing: 0.5px; }
                .text-custom-green { color: #82c341; }
                .text-custom-blue { color: #2da1db; }
                .text-custom-orange { color: #f37525; }
                
                /* Dashboard Layout */
                .dashboard-row { margin-top: 25px; margin-bottom: 25px; display: flex; flex-wrap: wrap; }
                
                /* FIX FOR GRAPH GROWING: Don't set min-height here, handle inside */
                .graph-container { background: #fff; padding: 15px; border: 1px solid #e5e5e5; border-radius: 4px; }
                
                .modes-container { background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; overflow: hidden; }
                .modes-header { background: #f9fafc; padding: 10px 15px; border-bottom: 1px solid #e5e5e5; font-weight: 600; color: #444; font-size: 13px; text-transform: uppercase; }
                .mode-item { padding: 12px 15px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
                .mode-item:last-child { border-bottom: none; }
                .mode-name { font-weight: 500; font-size: 13px; }
            </style>

            <div class="row">
                <div class="col-md-4">
                    <div class="stat-box">
                        <h3 class="text-custom-green"><?php echo app_format_money($stat_total_amount, $base_currency); ?></h3>
                        <span>Total Amount</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <h3 class="text-custom-blue"><?php echo number_format($stat_total_count); ?></h3>
                        <span>Total Issued</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box">
                        <h3 class="text-custom-orange"><?php echo number_format($stat_open_count); ?></h3>
                        <span>Open / Active</span>
                    </div>
                </div>
            </div>

            <div class="row dashboard-row">
                <div class="col-md-8">
                    <div class="graph-container">
                        <p class="text-muted" style="margin-bottom: 15px;">Credit Note Trend (Selected Period)</p>
                        <div style="position: relative; height: 300px; width: 100%;">
                            <canvas id="cnTrendChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="modes-container">
                        <div class="modes-header">By Status</div>
                        <?php if(empty($status_data)){ echo '<div style="padding:15px;text-align:center;">No data found</div>'; } ?>
                        
                        <?php foreach($status_data as $row){ 
                            $status_id = $row['status'];
                            $name = isset($status_map[$status_id]) ? $status_map[$status_id]['name'] : 'Unknown';
                            $color = isset($status_map[$status_id]) ? $status_map[$status_id]['color'] : '#000';
                        ?>
                            <div class="mode-item">
                                <div>
                                    <span class="mode-name" style="color:<?php echo $color; ?>"><?php echo $name; ?></span>
                                    <span class="text-muted" style="font-size:11px;">(<?php echo $row['count']; ?>)</span>
                                </div>
                                <span style="font-weight:600;"><?php echo app_format_money($row['amount'], $base_currency); ?></span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <hr class="hr-panel-heading" />

        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="date_range" class="control-label">Period</label>
                    <select class="form-control" id="date_range" name="date_range">
                        <option value="">Select Range...</option>
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_year">This Year</option>
                        <option value="last_year">Last Year</option>
                        <option value="all_time">All Time</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="report_from" class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
                    <div class="input-group date">
                        <input type="text" class="form-control datepicker" id="report_from" name="report_from" value="<?php echo $f_from; ?>">
                        <div class="input-group-addon"><i class="fa fa-calendar calendar-icon"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="report_to" class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
                    <div class="input-group date">
                        <input type="text" class="form-control datepicker" id="report_to" name="report_to" value="<?php echo $f_to; ?>">
                        <div class="input-group-addon"><i class="fa fa-calendar calendar-icon"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-2 mtop25">
                <button type="button" id="filter-submit" class="btn btn-info btn-block"><?php echo _l('filter'); ?></button>
            </div>
        </div>

        <div class="clearfix"></div>
        <hr class="hr-panel-heading" />

        <div class="row">
		    <div class="_filters _hidden_inputs">
			    <?php
			    foreach($statuses as $status) {
				    echo form_hidden('credit_notes_status_'.$status['id'],isset($status['filter_default'])
					    && $status['filter_default'] ? 'true' : '');
			    }
			    foreach($years as $year){
				    echo form_hidden('year_'.$year['year'],$year['year']);
			    }
			    ?>
		    </div>
		    <div class="col-md-12">
			    <div class="panel_s mbot10">
				    <div class="panel-body _buttons">
					    <?php if(has_permission('credit_notes','','create')){ ?>
					    <a href="<?php echo admin_url('credit_notes/credit_note'); ?>" class="btn btn-info pull-left display-block">
						    <?php echo _l('new_credit_note'); ?>
					    </a>
					    <?php } ?>
					    <div class="display-block text-right">
						    <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
							    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								    <i class="fa fa-filter" aria-hidden="true"></i>
							    </button>
							    <ul class="dropdown-menu width300">
								    <li>
									    <a href="#" data-cview="all" onclick="dt_custom_view('','.table-credit-notes',''); return false;">
										    <?php echo _l('credit_notes_list_all'); ?>
									    </a>
								    </li>
								    <li class="divider"></li>
								    <?php foreach($statuses as $status){ ?>
								    <li class="<?php if(isset($status['filter_default']) && $status['filter_default']){echo 'active';} ?>">
									    <a href="#" data-cview="credit_notes_status_<?php echo $status['id']; ?>" onclick="dt_custom_view('credit_notes_status_<?php echo $status['id']; ?>','.table-credit-notes','credit_notes_status_<?php echo $status['id']; ?>'); return false;">
										    <?php echo format_credit_note_status($status['id'],true); ?>
									    </a>
								    </li>
								    <?php } ?>
								    <div class="clearfix"></div>
								    <?php
								    if(count($years) > 0){ ?>
								    <li class="divider"></li>
								    <?php foreach($years as $year){ ?>
								    <li class="active">
									    <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-credit-notes','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
									    </a>
								    </li>
								    <?php } ?>
								    <?php } ?>
							    </ul>
						    </div>
						    <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-credit-notes','#credit_note'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
					    </div>
				    </div>
			    </div>
			    <div class="row">
				    <div class="col-md-12" id="small-table">
					    <div class="panel_s">
						    <div class="panel-body">
							    <?php echo form_hidden('credit_note_id',$credit_note_id); ?>
							    <?php $this->load->view('admin/credit_notes/table_html'); ?>
						    </div>
					    </div>
				    </div>
				    <div class="col-md-7 small-table-right-col">
					    <div id="credit_note" class="hide">
					    </div>
				    </div>
			    </div>
		    </div>
	    </div>
	</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>
	var hidden_columns = [4,5,6,7];
</script>
<?php init_tail(); ?>
<script>
    // --- DATE HELPER ---
    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [year, month, day].join('-');
    }

	$(function(){
        // --- 1. SETUP CHART ---
        var ctx = document.getElementById('cnTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Amount Issued',
                    data: <?php echo json_encode($chart_values); ?>,
                    borderColor: '#2da1db',
                    backgroundColor: 'rgba(45, 161, 219, 0.1)',
                    borderWidth: 2,
                    tension: 0.4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, // Important: allows chart to fill the 300px height
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // --- 2. FILTER LOGIC ---
		var Credit_Notes_ServerParams = {};
		$.each($('._hidden_inputs._filters input'),function(){
			Credit_Notes_ServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
		});
        // Add new date filters to Datatable params
        Credit_Notes_ServerParams['report_from'] = '[name="report_from"]';
        Credit_Notes_ServerParams['report_to'] = '[name="report_to"]';

		initDataTable('.table-credit-notes', admin_url+'credit_notes/table', ['undefined'], ['undefined'], Credit_Notes_ServerParams, [[1,'desc'], [0,'desc']]);
		init_credit_note();

        // --- 3. FILTER BUTTON CLICK ---
        $('#filter-submit').on('click', function() {
			var fromDate = $('input[name="report_from"]').val();
			var toDate = $('input[name="report_to"]').val();
            // Reload page so PHP Stats/Graph update
            var url = window.location.href.split('?')[0];
            window.location.href = url + '?report_from=' + fromDate + '&report_to=' + toDate;
		});

        // --- 4. DATE RANGE DROPDOWN ---
        $('#date_range').on('change', function() {
            var range = $(this).val();
            var today = new Date();
            var fromDate, toDate;

            if (range === 'all_time') {
                $('input[name="report_from"]').val('');
                $('input[name="report_to"]').val('');
                $('#filter-submit').click();
                return;
            }

            if (range === 'today') {
                fromDate = new Date();
                toDate = new Date();
            } else if (range === 'this_week') {
                var first = today.getDate() - today.getDay(); 
                fromDate = new Date(today.setDate(first));
                toDate = new Date();
            } else if (range === 'this_month') {
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            } else if (range === 'last_month') {
                fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                toDate = new Date(today.getFullYear(), today.getMonth(), 0);
            } else if (range === 'this_year') {
                fromDate = new Date(today.getFullYear(), 0, 1);
                toDate = new Date(today.getFullYear(), 11, 31);
            } else if (range === 'last_year') {
                fromDate = new Date(today.getFullYear() - 1, 0, 1);
                toDate = new Date(today.getFullYear() - 1, 11, 31);
            }

            if (fromDate && toDate) {
                $('input[name="report_from"]').val(formatDate(fromDate));
                $('input[name="report_to"]').val(formatDate(toDate));
                $('#filter-submit').click();
            }
        });
	});
</script>
</body>
</html>