<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="sales-aging-report" class="hide">
  <?php if($mysqlVersion && strpos($mysqlVersion->version,'5.6') !== FALSE && $sqlMode && strpos($sqlMode->mode,'ONLY_FULL_GROUP_BY') !== FALSE){ ?>
    <div class="alert alert-danger">
      Sales Aging Report may not work properly because ONLY_FULL_GROUP_BY is enabled, consult with your hosting provider to disable ONLY_FULL_GROUP_BY in sql_mode configuration. In case the report is working properly you can just ignore this message.
    </div>
  <?php } ?>
  <p class="mbot20 text-info"><?php echo _l('sales_aging_report_notice'); ?></p>
  <div class="row">
    <div class="col-md-4">
      <div class="form-group">
        <label for="sale_agent_items"><?php echo _l('Customer'); ?></label>
        <select name="sale_agent_items" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
          <?php foreach($invoices_sale_agents as $agent){ ?>
            <option value="<?php echo $agent['userid']; ?>"><?php echo $agent['company']; ?></option>
          <?php } ?>
        </select>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="form-group">
        <label for="sale_product_items"><?php echo _l('Product'); ?></label>
        <select name="sale_product_items" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
          <?php foreach($invoices_sale_product as $agent){ ?>
            <option value="<?php echo $agent['id']; ?>"><?php echo $agent['commodity_code'].'-'.$agent['description']; ?></option>
          <?php } ?>
        </select>
      </div>
    </div>
  </div>
  <table class="table table-sales-aging-report scroll-responsive">
    <thead>
      <tr>
        <th><?php echo _l('reports_item'); ?></th>
        <th><?php echo _l('invoice_number'); ?></th>
        <th><?php echo _l('client'); ?></th>
        <th><?php echo _l('invoice_date'); ?></th>
        <th><?php echo _l('invoice_due_date'); ?></th>
        <th><?php echo _l('days_overdue'); ?></th>
        <th><?php echo _l('aging_category'); ?></th>
        <th><?php echo _l('quantity_sold'); ?></th>
        <th><?php echo _l('rate'); ?></th>
        <th><?php echo _l('total_amount'); ?></th>
      </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="7" class="text-right"><strong><?php echo _l('total'); ?></strong></td>
        <td class="total_qty"></td>
        <td></td>
        <td class="total_amount"></td>
      </tr>
      <tr>
        <td colspan="6" class="text-right"><strong><?php echo _l('aging_summary'); ?></strong></td>
        <td class="text-right"><strong>1-30 <?php echo _l('days'); ?></strong></td>
        <td colspan="2"></td>
        <td class="aging_30"></td>
      </tr>
      <tr>
        <td colspan="6"></td>
        <td class="text-right"><strong>31-60 <?php echo _l('days'); ?></strong></td>
        <td colspan="2"></td>
        <td class="aging_60"></td>
      </tr>
      <tr>
        <td colspan="6"></td>
        <td class="text-right"><strong>61-90 <?php echo _l('days'); ?></strong></td>
        <td colspan="2"></td>
        <td class="aging_90"></td>
      </tr>
      <tr>
        <td colspan="6"></td>
        <td class="text-right"><strong>91-120 <?php echo _l('days'); ?></strong></td>
        <td colspan="2"></td>
        <td class="aging_120"></td>
      </tr>
      <tr>
        <td colspan="6"></td>
        <td class="text-right"><strong>120+ <?php echo _l('days'); ?></strong></td>
        <td colspan="2"></td>
        <td class="aging_older"></td>
      </tr>
    </tfoot>
  </table>
</div>