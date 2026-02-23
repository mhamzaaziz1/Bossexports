<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">

                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-12">
                        <?php if(has_permission('payments','','create')){ ?>
                            <a href="javascript:void(0)" onclick="record_payment_modal(); return false;" class="btn btn-success">
                            <i class="fa fa-plus-square"></i> <?php echo _l('payment'); ?></a>
                        <?php } ?>
                        
                        <button class="btn btn-default pull-right" type="button" data-toggle="collapse" data-target="#stats-collapse" aria-expanded="true" aria-controls="stats-collapse">
                            <i class="fa fa-bar-chart"></i> Toggle Stats
                        </button>
                    </div>
                </div>

                <div class="collapse in" id="stats-collapse" aria-expanded="true">
                    
                    <?php
                    // Get Date Filters from URL
                    $f_from = $this->input->get('payment_from');
                    $f_to   = $this->input->get('payment_to');

                    // --- QUERY 1: TOP STATS ---
                    $this->db->select('SUM(amount) as total_amount, COUNT(DISTINCT transactionid) as total_rows');
                    $this->db->from(db_prefix() . 'invoicepaymentrecords');
                    
                    if (!empty($f_from)) {
                        $this->db->where('date >=', to_sql_date($f_from));
                    }
                    if (!empty($f_to)) {
                        $this->db->where('date <=', to_sql_date($f_to));
                    }

                    $stats_query = $this->db->get()->row();

                    $stat_total_received = $stats_query ? $stats_query->total_amount : 0;
                    $stat_transactions   = $stats_query ? $stats_query->total_rows : 0;
                    $stat_avg            = ($stat_transactions > 0) ? ($stat_total_received / $stat_transactions) : 0;
                    $base_currency       = get_base_currency();

                    // --- QUERY 2: GRAPH DATA ---
                    $this->db->select("DATE_FORMAT(date, '%Y-%m') as m, SUM(amount) as total");
                    $this->db->from(db_prefix() . 'invoicepaymentrecords');
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

                    // --- QUERY 3: SIDE TABLE ---
                    $this->db->select('pm.name as mode_name, COUNT(ipr.id) as count, SUM(ipr.amount) as amount');
                    $this->db->from(db_prefix() . 'invoicepaymentrecords as ipr');
                    $this->db->join(db_prefix() . 'payment_modes as pm', 'pm.id = ipr.paymentmode', 'left');
                    
                    if (!empty($f_from)) $this->db->where('ipr.date >=', to_sql_date($f_from));
                    if (!empty($f_to))   $this->db->where('ipr.date <=', to_sql_date($f_to));
                    
                    $this->db->group_by('ipr.paymentmode');
                    $this->db->order_by('amount', 'DESC');
                    
                    $modes_data = $this->db->get()->result_array();
                    ?>

                    <style>
                        .stat-box { background: #fff; border: 1px solid #e5e5e5; padding: 25px 15px; text-align: center; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
                        .stat-box h3 { margin: 0 0 5px 0; font-weight: 700; font-size: 22px; }
                        .stat-box span { font-size: 11px; text-transform: uppercase; color: #777; letter-spacing: 0.5px; }
                        .text-custom-green { color: #82c341; }
                        .text-custom-blue { color: #2da1db; }
                        .text-custom-orange { color: #f37525; }
                        .dashboard-row { margin-top: 25px; margin-bottom: 25px; display: flex; flex-wrap: wrap; }
                        .graph-container { background: #fff; padding: 15px; border: 1px solid #e5e5e5; border-radius: 4px; min-height: 350px; }
                        .modes-container { background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; overflow: hidden; }
                        .modes-header { background: #f9fafc; padding: 10px 15px; border-bottom: 1px solid #e5e5e5; font-weight: 600; color: #444; font-size: 13px; text-transform: uppercase; }
                        .mode-item { padding: 12px 15px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
                        .mode-item:last-child { border-bottom: none; }
                        .mode-name { font-weight: 500; font-size: 13px; }
                        .mode-count { font-size: 11px; color: #999; margin-left: 5px; }
                        .mode-amount { font-weight: 600; color: #333; }
                    </style>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="stat-box">
                                <h3 class="text-custom-green"><?php echo app_format_money($stat_total_received, $base_currency); ?></h3>
                                <span>Total Received</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box">
                                <h3 class="text-custom-blue"><?php echo number_format($stat_transactions); ?></h3>
                                <span>Transactions</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box">
                                <h3 class="text-custom-orange"><?php echo app_format_money($stat_avg, $base_currency); ?></h3>
                                <span>Avg. Transaction</span>
                            </div>
                        </div>
                    </div>

                    <div class="row dashboard-row">
                        <div class="col-md-8">
                            <div class="graph-container">
                                <p class="text-muted" style="margin-bottom: 15px;">Collection Trend (Selected Period)</p>
                                <div style="position: relative; height: 300px; width: 100%;">
                                    <canvas id="paymentTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="modes-container">
                                <div class="modes-header">By Payment Mode</div>
                                <?php if(empty($modes_data)){ echo '<div style="padding:15px;text-align:center;">No data found</div>'; } ?>
                                <?php foreach($modes_data as $mode){ $name = $mode['mode_name'] ?? 'Unknown/Deleted'; ?>
                                    <div class="mode-item">
                                        <div>
                                            <span class="mode-name"><?php echo $name; ?></span>
                                            <span class="mode-count">(<?php echo $mode['count']; ?>)</span>
                                        </div>
                                        <span class="mode-amount"><?php echo app_format_money($mode['amount'], $base_currency); ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div> <hr class="hr-panel-heading" />
                  
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
                           <label for="payment_from" class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
                           <div class="input-group date">
                              <input type="text" class="form-control datepicker" id="payment_from" name="payment_from" value="<?php echo $f_from; ?>">
                              <div class="input-group-addon">
                                 <i class="fa fa-calendar calendar-icon"></i>
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="col-md-3">
                        <div class="form-group">
                           <label for="payment_to" class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
                           <div class="input-group date">
                              <input type="text" class="form-control datepicker" id="payment_to" name="payment_to" value="<?php echo $f_to; ?>">
                              <div class="input-group-addon">
                                 <i class="fa fa-calendar calendar-icon"></i>
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="col-md-2">
                        <div class="form-group">
                           <label for="amount" class="control-label"><?php echo _l('amount'); ?></label>
                           <input type="number" class="form-control" id="amount" name="amount">
                        </div>
                     </div>

                     <div class="col-md-2 mtop25">
                        <button type="button" id="filter-payments" class="btn btn-info btn-block"><?php echo _l('filter'); ?></button>
                     </div>
                  </div>
                  
                  <div class="clearfix"></div>
                  <hr class="hr-panel-heading" />

				<?php $this->load->view('admin/payments/table_html_all'); ?>
			</div>
		</div>

        <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="noteModalLabel">Add Note</h4>
              </div>
              <div class="modal-body">
                <form>
                  <div class="form-group">
                    <label for="note">Note:</label>
                    <textarea class="form-control" id="note" name="note"></textarea>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save-note">Save Note</button>
              </div>
            </div>
          </div>
        </div>

        <!-- ATTACH INVOICE MODAL -->
        <div class="modal fade" id="attachInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="attachInvoiceModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="attachInvoiceModalLabel"><?php echo _l('Attach Invoice'); ?></h4>
              </div>
              <div class="modal-body">
                  <div class="form-group">
                    <label for="invoice_id_attach"><?php echo _l('Select Invoice'); ?></label>
                    <select class="form-control" id="invoice_id_attach" name="invoice_id">
                        <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                    </select>
                  </div>
                  <div id="attach-invoice-response"></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary" id="save-attach-invoice"><?php echo _l('save'); ?></button>
              </div>
            </div>
          </div>
        </div>

        <!-- VIEW PAYMENTS MODAL -->
        <div class="modal fade" id="viewPaymentsModal" tabindex="-1" role="dialog" aria-labelledby="viewPaymentsModalLabel">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="viewPaymentsModalLabel"><?php echo _l('Payments'); ?></h4>
              </div>
              <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="view-payments-table">
                        <thead>
                            <tr>
                                <th><?php echo _l('id'); ?></th>
                                <th><?php echo _l('invoice'); ?></th>
                                <th><?php echo _l('payment_mode'); ?></th>
                                <th><?php echo _l('transaction_id'); ?></th>
                                <th><?php echo _l('amount'); ?></th>
                                <th><?php echo _l('date'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data -->
                        </tbody>
                    </table>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
              </div>
            </div>
          </div>
        </div>

        <!-- PAYMENT DETAIL MODAL -->
        <div class="modal fade" id="paymentDetailModal" tabindex="-1" role="dialog" aria-labelledby="paymentDetailModalLabel">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-body" id="payment-receipt-content">
                  <div class="text-center">Loading...</div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
              </div>
            </div>
          </div>
        </div>

        <!-- PAYMENT EDIT MODAL -->
        <div class="modal fade" id="paymentEditModal" tabindex="-1" role="dialog" aria-labelledby="paymentEditModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="paymentEditModalLabel"><?php echo _l('edit'); ?></h4>
              </div>
              <div class="modal-body" id="payment-edit-content">
                  <div class="text-center">Loading...</div>
              </div>
            </div>
          </div>
        </div>

        <!-- PAYMENT ADD MODAL -->
        <div class="modal fade" id="paymentAddModal" tabindex="-1" role="dialog" aria-labelledby="paymentAddModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="paymentAddModalLabel"><?php echo _l('payment'); ?></h4>
              </div>
              <div class="modal-body" id="payment-add-content">
                  <div class="text-center">Loading...</div>
              </div>
            </div>
          </div>
        </div>
	</div>
</div>
<?php init_tail(); ?>

<script>
    // --- DATE HELPER FUNCTION ---
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
        // --- 1. SETUP GRAPH ---
        var ctx = document.getElementById('paymentTrendChart').getContext('2d');
        var chartData = {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Total Received',
                data: <?php echo json_encode($chart_values); ?>,
                borderColor: '#84c529',
                backgroundColor: 'rgba(132, 197, 41, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#84c529',
                tension: 0.4
            }]
        };
        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

		// --- 2. EXISTING TABLE LOGIC ---
		var fnServerParams = {
			"report_months": function() { return 'custom'; },
			"report_from": 'input[name="payment_from"]',
			"report_to": 'input[name="payment_to"]',
			"amount": 'input[name="amount"]'
		};

		var paymentsTable = initDataTable('.table-payments', admin_url+'payments/table_all', undefined, undefined, fnServerParams, <?php echo hooks()->apply_filters('payments_table_default_order', json_encode(array(0,'desc'))); ?>);

		// --- 3. UPDATED FILTER LOGIC ---
		$('#filter-payments').on('click', function() {
			var fromDate = $('input[name="payment_from"]').val();
			var toDate = $('input[name="payment_to"]').val();
            // Reload page with query params so Stats update
            var url = window.location.href.split('?')[0];
            window.location.href = url + '?payment_from=' + fromDate + '&payment_to=' + toDate;
		});

        // --- 4. NEW DROPDOWN RANGE LOGIC ---
        $('#date_range').on('change', function() {
            var range = $(this).val();
            var today = new Date();
            var fromDate, toDate;

            if (range === 'all_time') {
                $('input[name="payment_from"]').val('');
                $('input[name="payment_to"]').val('');
                $('#filter-payments').click();
                return;
            }

            if (range === 'today') {
                fromDate = new Date();
                toDate = new Date();
            } else if (range === 'this_week') {
                var first = today.getDate() - today.getDay(); // First day is the day of the month - the day of the week
                fromDate = new Date(today.setDate(first));
                toDate = new Date(); // Today
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
                $('input[name="payment_from"]').val(formatDate(fromDate));
                $('input[name="payment_to"]').val(formatDate(toDate));
                // Auto-trigger filter
                $('#filter-payments').click();
            }
        });
	});

  // Note Modal Script
  $('#noteModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    var modal = $(this);
    modal.find('.modal-body #note').val('');
    modal.find('.modal-footer #save-note').unbind('click').click(function() {
      var note = modal.find('.modal-body #note').val();
      $.ajax({
        type: "POST",
        url: "save_note.php",
        data: {id: id, note: note},
        success: function(data) {
          modal.modal('hide');
          alert("Note saved successfully!");
        }
      });
    });
  });

  // Attach Invoice Modal Script
  $('#attachInvoiceModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var paymentId = button.data('id');
    var clientId = button.data('client-id');
    var modal = $(this);
    var select = modal.find('#invoice_id_attach');
    
    // Reset
    select.html('<option value="">Loading...</option>');
    modal.find('#attach-invoice-response').html('');

    // Fetch invoices
    $.ajax({
        url: admin_url + 'payments/get_invoice_unpaid',
        type: 'POST',
        data: {vid: clientId},
        dataType: 'json',
        success: function(response) {
            var options = '<option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>';
            if(response && response.length > 0) {
                $.each(response, function(i, item) {
                    options += '<option value="'+item.id+'">' + item.pur_order_number + '</option>';
                });
            } else {
                options = '<option value="">No unpaid invoices found</option>';
            }
            select.html(options);
        }
    });

    modal.find('#save-attach-invoice').unbind('click').click(function() {
        var invoiceId = select.val();
        if(!invoiceId) {
            alert('Please select an invoice');
            return;
        }

        $.ajax({
            url: admin_url + 'payments/attach_invoice/' + paymentId,
            type: 'POST',
            data: {invoiceid: invoiceId},
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    modal.modal('hide');
                    // Reload table
                    $('.table-payments').DataTable().ajax.reload();
                } else {
                    alert(response.message);
                }
            }
        });
    });
  });
  // View Payments Modal Script
  $('#viewPaymentsModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var transactionId = button.data('transaction-id');
    var date = button.data('date');
    var modal = $(this);
    var tbody = modal.find('#view-payments-table tbody');
    
    tbody.html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');

    $.ajax({
        url: admin_url + 'payments/get_grouped_payments',
        type: 'POST',
        data: {transactionid: transactionId, date: date},
        dataType: 'json',
        success: function(response) {
            var html = '';
            if(response && response.length > 0) {
                $.each(response, function(i, item) {
                    html += '<tr>';
                    // Changed to trigger modal
                    html += '<td><a href="javascript:void(0)" onclick="view_payment_modal('+item.id+')">' + item.id + '</a></td>';
                    html += '<td><a href="' + item.invoice_link + '">' + item.invoice_number + '</a></td>';
                    html += '<td>' + (item.mode_name ? item.mode_name : '') + '</td>';
                    html += '<td>' + item.transactionid + '</td>';
                    html += '<td>' + item.amount_formatted + '</td>';
                    html += '<td>' + item.date + '</td>';
                    html += '</tr>';
                });
            } else {
                html = '<tr><td colspan="6" class="text-center">No payments found</td></tr>';
            }
            tbody.html(html);
        }
    });
  });

  // Function to View Payment Detail
  function view_payment_modal(id) {
      var modal = $('#paymentDetailModal');
      var content = modal.find('#payment-receipt-content');
      
      content.html('<div class="text-center">Loading...</div>');
      modal.modal('show');
      
      $.get(admin_url + 'payments/get_payment_receipt_html/' + id, function(response) {
          content.html(response);
      });
  }

  // Function to trigger Email Modal
  function send_payment_to_email(id) {
        $('#paymentDetailModal').modal('hide');
        requestGet('payments/get_send_to_client_modal/' + id).done(function(response) {
            $('body').append(response);
            init_selectpicker();
            init_editor();
            $('#payment_send_to_client').modal('show');
            $('#payment_send_to_client').on('hidden.bs.modal', function(e) {
                $('#payment_send_to_client').remove();
            });
        });
  }
  // Function to View Payment Edit Modal
  function edit_payment_modal(id) {
      $('#paymentDetailModal').modal('hide');
      var modal = $('#paymentEditModal');
      var content = modal.find('#payment-edit-content');
      
      content.html('<div class="text-center">Loading...</div>');
      modal.modal('show');
      
      $.get(admin_url + 'payments/get_payment_edit_html/' + id, function(response) {
          content.html(response);
          // Re-bind form submission
          $('#payment-edit-form').on('submit', function(e) {
              e.preventDefault();
              var form = $(this);
              $.ajax({
                  url: form.attr('action'),
                  type: 'POST',
                  data: form.serialize(),
                  dataType: 'json',
                  success: function(res) {
                      if(res.success) {
                          alert_float('success', res.message);
                          modal.modal('hide');
                          // Reload table
                          $('.table-payments').DataTable().ajax.reload();
                      } else {
                          alert_float('danger', res.message);
                      }
                  }
              });
          });
      });
  }

  // Function to View Payment Add Modal
  function record_payment_modal() {
      var modal = $('#paymentAddModal');
      var content = modal.find('#payment-add-content');
      
      content.html('<div class="text-center">Loading...</div>');
      modal.modal('show');
      
      $.get(admin_url + 'payments/get_payment_add_html', function(response) {
          content.html(response);
          init_selectpicker();
          init_datepicker();
          init_ajax_search('customer', '#clientid_add');
          
          // Handle customer change to fetch invoices
          $('#clientid_add').on('change', function() {
              var vid = $(this).val();
              if(vid) {
                  $.post(admin_url + 'payments/get_invoice_unpaid', {vid: vid}, function(res) {
                      var invoices = JSON.parse(res);
                      var options = '';
                      $.each(invoices, function(i, inv) {
                          options += '<option value="' + inv.id + '">' + inv.number + ' (Left: ' + inv.total_left_to_pay + ')</option>';
                      });
                      $('#pur_order_add').html(options);
                      $('#pur_order_add').selectpicker('refresh');
                  });
              } else {
                  $('#pur_order_add').html('');
                  $('#pur_order_add').selectpicker('refresh');
              }
          });

          // Handle form submission
          $('#add-payment-form').on('submit', function(e) {
              e.preventDefault();
              var form = $(this);
              $.ajax({
                  url: admin_url + 'payments/add_payment_ajax',
                  type: 'POST',
                  data: form.serialize(),
                  dataType: 'json',
                  success: function(res) {
                      if(res.success) {
                          alert_float('success', res.message);
                          modal.modal('hide');
                          $('.table-payments').DataTable().ajax.reload();
                      } else {
                          alert_float('danger', res.message);
                      }
                  }
              });
          });
      });
  }
</script>
</body>
</html>