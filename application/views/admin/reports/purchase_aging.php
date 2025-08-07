<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin font-medium"><i class="fa fa-shopping-cart" aria-hidden="true"></i> <?php echo _l('purchase_aging_report'); ?></h4>
                  <hr />
                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="purchase_status"><?php echo _l('purchase_status'); ?></label>
                           <select name="purchase_status" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('all_statuses'); ?>">
                              <?php 
                              $statuses = [1, 2, 3, 4]; // Define your purchase order statuses here
                              foreach($statuses as $status){ ?>
                              <option value="<?php echo $status; ?>"><?php echo format_purchase_status($status,'',false) ?></option>
                              <?php } ?>
                           </select>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="vendor_id"><?php echo _l('vendor'); ?></label>
                           <select name="vendor_id" class="selectpicker" multiple data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('all_vendors'); ?>">
                              <?php 
                              // We'll load vendors via AJAX
                              ?>
                           </select>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="report_from" class="control-label"><?php echo _l('report_from_date'); ?></label>
                           <div class="input-group date">
                              <input type="text" class="form-control datepicker" name="report_from">
                              <div class="input-group-addon">
                                 <i class="fa fa-calendar calendar-icon"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="report_to" class="control-label"><?php echo _l('report_to_date'); ?></label>
                           <div class="input-group date">
                              <input type="text" class="form-control datepicker" name="report_to">
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

                  <!-- Purchase Aging Report Table -->
                  <div class="table-responsive">
                     <table class="table table-purchase-aging-report scroll-responsive">
                        <thead>
                           <tr>
                              <th><?php echo _l('purchase_date'); ?></th>
                              <th><?php echo _l('purchase_status'); ?></th>
                              <th><?php echo _l('purchase_number'); ?></th>
                              <th><?php echo _l('vendor_name'); ?></th>
                              <th><?php echo _l('item_description'); ?></th>
                              <th><?php echo _l('quantity'); ?></th>
                              <th><?php echo _l('unit_price'); ?></th>
                              <th><?php echo _l('total_price'); ?></th>
                              <th><?php echo _l('payment_status'); ?></th>
                              <th><?php echo _l('days_aging'); ?></th>
                              <th><?php echo _l('aging_bracket'); ?></th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                           <tr>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td class="total_quantity"></td>
                              <td></td>
                              <td class="total_price"></td>
                              <td></td>
                              <td></td>
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

   // Load vendors via AJAX
   $.get(admin_url + 'purchase/get_vendors', function(response) {
      var vendors = JSON.parse(response);
      var options = '';
      $.each(vendors, function(i, vendor) {
         options += '<option value="' + vendor.id + '">' + vendor.company + '</option>';
      });
      $('select[name="vendor_id"]').html(options).selectpicker('refresh');
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

      generatePurchaseAgingReport();
   });

   // Export to Excel button click handler
   $('#export-excel').on('click', function(e) {
      e.preventDefault();
      var table = $('.table-purchase-aging-report').DataTable();
      table.button('.buttons-excel').trigger();
   });

   // Export to PDF button click handler
   $('#export-pdf').on('click', function(e) {
      e.preventDefault();
      var table = $('.table-purchase-aging-report').DataTable();
      table.button('.buttons-pdf').trigger();
   });

   // Initialize the DataTable
   function generatePurchaseAgingReport() {
      // Initialize the table
      if ($.fn.DataTable.isDataTable('.table-purchase-aging-report')) {
         $('.table-purchase-aging-report').DataTable().destroy();
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
         "purchase_status": 'select[name="purchase_status"]',
         "vendor_id": 'select[name="vendor_id"]'
      };

      // Initialize the table
      initDataTable('.table-purchase-aging-report', admin_url + 'reports/purchase_aging_report', false, false, fnServerParams, [0, 'desc']);

      // Add event handler for DataTable draw event to update footer
      $('.table-purchase-aging-report').on('draw.dt', function() {
         var table = $(this).DataTable();
         var sums = table.ajax.json().sums;
         if (sums) {
            $(this).find('tfoot').addClass('bold');
            $(this).find('tfoot td.total_quantity').html(sums.total_quantity);
            $(this).find('tfoot td.total_price').html(sums.total_price);
         }
      });
   }

   // Load the report on page load if dates are set
   if ($('input[name="report_from"]').val() && $('input[name="report_to"]').val()) {
      generatePurchaseAgingReport();
   }
});
</script>