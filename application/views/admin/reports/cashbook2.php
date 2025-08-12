<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin font-medium"><i class="fa fa-book" aria-hidden="true"></i> <?php echo _l('cashbook2_report'); ?></h4>
                  <hr />
                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="invoice_status"><?php echo _l('report_invoice_status'); ?></label>
                           <select name="invoice_status" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
                              <?php 
                              $statuses = [1, 2, 3, 4]; // Unpaid, Paid, Partially Paid, Overdue
                              foreach($statuses as $status){ ?>
                              <option value="<?php echo $status; ?>"><?php echo format_invoice_status($status,'',false) ?></option>
                              <?php } ?>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="customer_id"><?php echo _l('customer'); ?></label>
                           <select name="customer_id" class="selectpicker" multiple data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
                              <?php 
                              // We'll load customers via AJAX
                              ?>
                           </select>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="report_from" class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
                           <div class="input-group date">
                              <input type="text" class="form-control datepicker"  name="report_from">
                              <div class="input-group-addon">
                                 <i class="fa fa-calendar calendar-icon"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="report_to" class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
                           <div class="input-group date">
                              <input type="text" class="form-control datepicker"  name="report_to">
                              <div class="input-group-addon">
                                 <i class="fa fa-calendar calendar-icon"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-4 mtop25">
                        <button type="button" id="generate-report" class="btn btn-info"><?php echo _l('generate_report'); ?></button>
                        <div class="btn-group mleft5">
                           <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <?php echo _l('export'); ?> <span class="caret"></span>
                           </button>
                           <ul class="dropdown-menu">
                              <li><a href="#" id="export-excel"><i class="fa fa-file-excel-o"></i> <?php echo _l('export_to_excel'); ?></a></li>
                              <li><a href="#" id="export-pdf"><i class="fa fa-file-pdf-o"></i> <?php echo _l('export_to_pdf'); ?></a></li>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <hr class="hr-panel-heading" />

                  <!-- Cashbook2 Report Table -->
                  <div class="table-responsive">
                     <table class="table table-cashbook2-report scroll-responsive">
                        <thead>
                           <tr>
                              <th><?php echo _l('date'); ?></th>
                              <th><?php echo _l('balance_bf'); ?></th>
                              <th><?php echo _l('sales_order'); ?></th>
                              <th><?php echo _l('invoice'); ?></th>
                              <th><?php echo _l('payment'); ?></th>
                              <th><?php echo _l('balance_cf'); ?></th>
                              <th><?php echo _l('running_balance'); ?></th>
                              <th><?php echo _l('director_note'); ?></th>
                              <th class="not-visible"><?php echo _l('customer'); ?></th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                           <tr>
                              <td><strong><?php echo _l('grand_total'); ?></strong></td>
                              <td></td>
                              <td class="sales_order_amount"></td>
                              <td class="invoice_amount"></td>
                              <td class="total_amount_paid"></td>
                              <td></td>
                              <td class="total_invoice_due"></td>
                              <td></td>
                              <td class="not-visible"></td>
                           </tr>
                        </tfoot>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script>
$(function() {
   // Set today's date as default if no date is selected
   var today = new Date();

   // Only set default dates if they're not already set
   if (!$('input[name="report_from"]').val()) {
      $('input[name="report_from"]').datepicker('setDate', today);
   }
   if (!$('input[name="report_to"]').val()) {
      $('input[name="report_to"]').datepicker('setDate', today);
   }

   // Load customers via AJAX
   $.get(admin_url + 'clients/get_clients', function(response) {
      var customers = JSON.parse(response);
      var options = '';
      $.each(customers, function(i, customer) {
         options += '<option value="' + customer.userid + '">' + customer.company + '</option>';
      });
      $('select[name="customer_id"]').html(options).selectpicker('refresh');
   });

   // Generate report button click handler
   $('#generate-report').on('click', function() {
      // Ensure the date values are set before generating the report
      if (!$('input[name="report_from"]').val()) {
         var today = new Date();
         $('input[name="report_from"]').datepicker('setDate', today);
      }
      if (!$('input[name="report_to"]').val()) {
         var today = new Date();
         $('input[name="report_to"]').datepicker('setDate', today);
      }

      // Update the report_months parameter to ensure it's always 'custom'
      if ($('#report_months_param').length) {
         $('#report_months_param').val('custom');
      }

      generateCashbook2Report();
   });

   // Export to Excel button click handler
   $('#export-excel').on('click', function(e) {
      e.preventDefault();
      var table = $('.table-cashbook2-report').DataTable();
      table.button('.buttons-excel').trigger();
   });

   // Export to PDF button click handler
   $('#export-pdf').on('click', function(e) {
      e.preventDefault();
      var table = $('.table-cashbook2-report').DataTable();
      table.button('.buttons-pdf').trigger();
   });

   // Initialize the DataTable
   function generateCashbook2Report() {
      // Initialize the table
      if ($.fn.DataTable.isDataTable('.table-cashbook2-report')) {
         $('.table-cashbook2-report').DataTable().destroy();
      }

      // Force set the date if it's empty
      if (!$('input[name="report_from"]').val()) {
         var today = new Date();
         $('input[name="report_from"]').datepicker('setDate', today);
      }

      // Force set the to date if it's empty
      if (!$('input[name="report_to"]').val()) {
         var today = new Date();
         $('input[name="report_to"]').datepicker('setDate', today);
      }

      // Create hidden inputs for the parameters
      if (!$('#report_months_param').length) {
         $('<input>').attr({
            type: 'hidden',
            id: 'report_months_param',
            name: 'report_months_param',
            value: 'custom'
         }).appendTo('body');
      } else {
         $('#report_months_param').val('custom');
      }

      // Define server parameters as an object where keys are parameter names and values are jQuery selectors
      var fnServerParams = {
         "report_months": '#report_months_param',
         "report_from": 'input[name="report_from"]',
         "report_to": 'input[name="report_to"]',
         "invoice_status": 'select[name="invoice_status"]',
         "customer_id": 'select[name="customer_id"]'
      };

      // Initialize the table with rowGroup extension for grouping by customer
      var table = $('.table-cashbook2-report').DataTable({
         processing: true,
         serverSide: true,
         ajax: {
            url: admin_url + 'reports/cashbook2_report',
            type: 'POST',
            data: function(d) {
               $.each(fnServerParams, function(key, value) {
                  d[key] = $(value).val();
               });
            }
         },
         order: [[8, 'asc'], [0, 'asc']], // Order by customer name, then by date
         rowGroup: {
            dataSrc: 8, // Customer name column (hidden)
            startRender: function(rows, group) {
               return '<span class="group-header">' + group + '</span>';
            }
         },
         columnDefs: [
            { targets: 8, visible: false }, // Hide customer name column
            { targets: '_all', orderable: false } // Disable ordering for all columns
         ],
         dom: 'Bfrtip',
         buttons: [
            {
               extend: 'excel',
               text: app.lang.export_to_excel,
               className: 'btn btn-default buttons-excel',
               exportOptions: {
                  columns: [0, 1, 2, 3, 4, 5, 6, 7]
               }
            },
            {
               extend: 'pdf',
               text: app.lang.export_to_pdf,
               className: 'btn btn-default buttons-pdf',
               exportOptions: {
                  columns: [0, 1, 2, 3, 4, 5, 6, 7]
               }
            }
         ],
         pageLength: 25,
         "footerCallback": function(row, data, start, end, display) {
            var api = this.api();
            var sums = api.ajax.json().sums;
            
            if (sums) {
               $(api.column(2).footer()).html(sums.sales_order_amount);
               $(api.column(3).footer()).html(sums.invoice_amount);
               $(api.column(4).footer()).html(sums.total_amount_paid);
               $(api.column(6).footer()).html(sums.total_invoice_due);
            }
         }
      });

      // Add event handler for DataTable draw event to update footer
      $('.table-cashbook2-report').on('draw.dt', function() {
         var cashbookTable = $(this).DataTable();
         var sums = cashbookTable.ajax.json().sums;
         if (sums) {
            $(this).find('tfoot').addClass('bold');
            // Map field names to what the table expects
            $(this).find('tfoot td.sales_order_amount').html(sums.sales_order_amount);
            $(this).find('tfoot td.invoice_amount').html(sums.invoice_amount);
            $(this).find('tfoot td.total_amount_paid').html(sums.total_amount_paid);
            $(this).find('tfoot td.total_invoice_due').html(sums.total_invoice_due);
         }
      });
   }

   // Load the report on page load if dates are set
   if ($('input[name="report_from"]').val() && $('input[name="report_to"]').val()) {
      generateCashbook2Report();
   }
});
</script>
<style>
.group-header {
   font-weight: bold;
   font-size: 1.1em;
   background-color: #f9f9f9;
   padding: 5px;
}
.not-visible {
   display: none;
}
</style>