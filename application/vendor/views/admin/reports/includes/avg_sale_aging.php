<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="avg-sale-aging-report" class="hide">
  <?php if(isset($mysqlVersion) && $mysqlVersion && strpos($mysqlVersion->version,'5.6') !== FALSE && isset($sqlMode) && $sqlMode && strpos($sqlMode->mode,'ONLY_FULL_GROUP_BY') !== FALSE){ ?>
    <div class="alert alert-danger">
      AVG Sale Aging Report may not work properly because ONLY_FULL_GROUP_BY is enabled, consult with your hosting provider to disable ONLY_FULL_GROUP_BY in sql_mode configuration. In case the report is working properly you can just ignore this message.
    </div>
  <?php } ?>
  <p class="mbot20 text-info"><?php echo _l('avg_sale_aging_report_notice'); ?></p>
  <div class="row">
    <div class="col-md-4">
      <div class="form-group">
        <label for="sale_agent_items"><?php echo _l('Customer'); ?></label>
        <select name="sale_agent_items" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
          <?php if(isset($invoices_sale_agents) && is_array($invoices_sale_agents)): ?>
            <?php foreach($invoices_sale_agents as $agent): ?>
              <option value="<?php echo $agent['userid']; ?>"><?php echo $agent['company']; ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="form-group">
        <label for="sale_product_items"><?php echo _l('Product'); ?></label>
        <select name="sale_product_items" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
          <?php if(isset($invoices_sale_product) && is_array($invoices_sale_product)): ?>
            <?php foreach($invoices_sale_product as $agent): ?>
              <option value="<?php echo $agent['id']; ?>"><?php echo $agent['commodity_code'].'-'.$agent['description']; ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
    </div>
  </div>
  <table class="table table-avg-sale-aging-report scroll-responsive">
    <thead>
      <tr>
        <th><?php echo _l('reports_item'); ?></th>
        <th><?php echo _l('invoice_count'); ?></th>
        <th><?php echo _l('avg_days_overdue'); ?></th>
        <th><?php echo _l('aging_category'); ?></th>
        <th><?php echo _l('total_quantity'); ?></th>
        <th><?php echo _l('avg_rate'); ?></th>
        <th><?php echo _l('total_amount'); ?></th>
      </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3" class="text-right"><strong><?php echo _l('total'); ?></strong></td>
        <td class="total_items"></td>
        <td class="total_qty"></td>
        <td></td>
        <td class="total_amount"></td>
      </tr>
      <tr>
        <td colspan="2" class="text-right"><strong><?php echo _l('overall_avg_days_overdue'); ?></strong></td>
        <td class="avg_days" colspan="5"></td>
      </tr>
    </tfoot>
  </table>
</div>
