<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin font-medium"><i class="fa fa-book" aria-hidden="true"></i> <?php echo _l('cashbook_report'); ?></h4>
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
                  <div class="table-responsive">
                     <table class="table table-cashbook-report scroll-responsive">
                        <thead>
                           <tr>
                              <th><?php echo _l('date'); ?></th>
                              <th><?php echo _l('report_invoice_status'); ?></th>
                              <th><?php echo _l('report_invoice_number'); ?></th>
                              <th><?php echo _l('report_invoice_customer'); ?></th>
                              <th><?php echo _l('invoice_amount'); ?></th>
                              <th><?php echo _l('cash_paid'); ?></th>
                              <th><?php echo _l('total_amount_paid'); ?></th>
                              <th><?php echo _l('today_amount_due'); ?></th>
                              <th><?php echo _l('total_invoice_due'); ?></th>
                              <th><?php echo _l('sales_order'); ?></th>
                              <th><?php echo _l('zim_account'); ?></th>
                              <th><?php echo _l('credit_note'); ?></th>
                              <th><?php echo _l('bank'); ?></th>
                              <th><?php echo _l('cash'); ?></th>
                              <?php
                              // Add column headers for all payment modes
                              $CI =& get_instance();
                              $CI->load->model('payment_modes_model');
                              $payment_modes = $CI->payment_modes_model->get();
                              foreach ($payment_modes as $mode) {
                                 echo '<th>' . $mode['name'] . '</th>';
                              }
                              ?>
                              <th><?php echo _l('credit_bf'); ?></th>
                              <th><?php echo _l('credit_cf'); ?></th>
                              <th><?php echo _l('total'); ?></th>
                              <th><?php echo _l('director_note'); ?></th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                           <tr>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td class="invoice_amount"></td>
                              <td class="cash_paid"></td>
                              <td class="total_amount_paid"></td>
                              <td class="today_amount_due"></td>
                              <td class="total_invoice_due"></td>
                              <td></td>
                              <td class="zim_account"></td>
                              <td class="credit_note"></td>
                              <td class="bank"></td>
                              <td class="cash"></td>
                              <?php
                              // Add footer cells for all payment modes
                              foreach ($payment_modes as $mode) {
                                 echo '<td class="payment_mode_' . $mode['id'] . '"></td>';
                              }
                              ?>
                              <td class="credit_bf"></td>
                              <td class="credit_cf"></td>
                              <td class="total_balance"></td>
                              <td></td>
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

   // Set default dates for vendor payment filters
   if (!$('input[name="vendor_payment_from"]').val()) {
      $('input[name="vendor_payment_from"]').datepicker('setDate', today);
   }
   if (!$('input[name="vendor_payment_to"]').val()) {
      $('input[name="vendor_payment_to"]').datepicker('setDate', today);
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

      // Ensure vendor payment date values are set
      if (!$('input[name="vendor_payment_from"]').val()) {
         var today = new Date();
         $('input[name="vendor_payment_from"]').datepicker('setDate', today);
      }
      if (!$('input[name="vendor_payment_to"]').val()) {
         var today = new Date();
         $('input[name="vendor_payment_to"]').datepicker('setDate', today);
      }

      // Update the report_months parameter to ensure it's always 'custom'
      if ($('#report_months_param').length) {
         $('#report_months_param').val('custom');
      }

      generateCashbookReport();
   });

   // Export to Excel button click handler
   $('#export-excel').on('click', function(e) {
      e.preventDefault();
      var table = $('.table-cashbook-report').DataTable();
      table.button('.buttons-excel').trigger();
   });

   // Export to PDF button click handler
   $('#export-pdf').on('click', function(e) {
      e.preventDefault();
      var table = $('.table-cashbook-report').DataTable();
      table.button('.buttons-pdf').trigger();
   });

   // Initialize the DataTable
   function generateCashbookReport() {
      if ($.fn.DataTable.isDataTable('.table-cashbook-report')) {
         $('.table-cashbook-report').DataTable().destroy();
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

      // Create a function that returns the current value when called
      function getReportFrom() {
         return $('input[name="report_from"]').val();
      }

      function getReportTo() {
         return $('input[name="report_to"]').val();
      }

      function getInvoiceStatus() {
         return $('select[name="invoice_status"]').val();
      }

      function getCustomerId() {
         return $('select[name="customer_id"]').val();
      }

      function getVendorPaymentFrom() {
         return $('input[name="vendor_payment_from"]').val();
      }

      function getVendorPaymentTo() {
         return $('input[name="vendor_payment_to"]').val();
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
         "customer_id": 'select[name="customer_id"]',
         "vendor_payment_from": 'input[name="vendor_payment_from"]',
         "vendor_payment_to": 'input[name="vendor_payment_to"]'
      };

      initDataTable('.table-cashbook-report', admin_url + 'reports/cashbook_report', false, false, fnServerParams, [0, 'desc']);

      // Add event handler for DataTable draw event to update footer
      $('.table-cashbook-report').on('draw.dt', function() {
         var cashbookTable = $(this).DataTable();
         var sums = cashbookTable.ajax.json().sums;
         if (sums) {
            $(this).find('tfoot').addClass('bold');
            $(this).find('tfoot td.invoice_amount').html(sums.invoice_amount);
            $(this).find('tfoot td.cash_paid').html(sums.cash_paid);
            $(this).find('tfoot td.total_amount_paid').html(sums.total_amount_paid);
            $(this).find('tfoot td.today_amount_due').html(sums.today_amount_due);
            $(this).find('tfoot td.total_invoice_due').html(sums.total_invoice_due);
            $(this).find('tfoot td.zim_account').html(sums.zim_account);
            $(this).find('tfoot td.credit_note').html(sums.credit_note);
            $(this).find('tfoot td.bank').html(sums.bank);
            $(this).find('tfoot td.cash').html(sums.cash);

            // Update payment mode columns
            <?php foreach ($payment_modes as $mode) : ?>
            $(this).find('tfoot td.payment_mode_<?php echo $mode['id']; ?>').html(sums.payment_mode_<?php echo $mode['id']; ?>);
            <?php endforeach; ?>
            $(this).find('tfoot td.credit_bf').html(sums.credit_bf);
            $(this).find('tfoot td.credit_cf').html(sums.credit_cf);
            $(this).find('tfoot td.total_balance').html(sums.total_balance);
         }
      });
   }

   // Load the report on page load if dates are set
   if ($('input[name="report_from"]').val() && $('input[name="report_to"]').val()) {
      generateCashbookReport();
   }
});
</script>
