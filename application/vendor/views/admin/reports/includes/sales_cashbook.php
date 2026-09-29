<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="cashbook-report" class="hide">
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
      <div class="clearfix"></div>
   </div>
</div>
<table class="table table-cashbook-report scroll-responsive">
   <thead>
      <tr>
         <th><?php echo _l('date'); ?></th>
         <th><?php echo _l('report_invoice_status'); ?></th>
         <th><?php echo _l('report_invoice_number'); ?></th>
         <th><?php echo _l('report_invoice_customer'); ?></th>
         <th><?php echo _l('invoice_amount'); ?></th>
         <th><?php echo _l('cash_paid'); ?></th>
         <th><?php echo _l('cash_paid_out'); ?></th>
         <th><?php echo _l('vat_refunded'); ?></th>
         <th><?php echo _l('sales_order'); ?></th>
         <th><?php echo _l('sales_order_amount_due'); ?></th>
         <th><?php echo _l('zim_account'); ?></th>
         <th><?php echo _l('credit_note'); ?></th>
         <th><?php echo _l('bank'); ?></th>
         <th><?php echo _l('cash'); ?></th>
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
         <td class="cash_paid_out"></td>
         <td class="vat_refunded"></td>
         <td></td>
         <td class="amount_due"></td>
         <td class="zim_account"></td>
         <td class="credit_note"></td>
         <td class="bank"></td>
         <td class="cash"></td>
         <td class="credit_bf"></td>
         <td class="credit_cf"></td>
         <td class="total_balance"></td>
         <td></td>
      </tr>
   </tfoot>
</table>
