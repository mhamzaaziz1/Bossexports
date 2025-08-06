<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="items-report" class="hide">
  <?php if($mysqlVersion && strpos($mysqlVersion->version,'5.6') !== FALSE && $sqlMode && strpos($sqlMode->mode,'ONLY_FULL_GROUP_BY') !== FALSE){ ?>
    <div class="alert alert-danger">
      Sales Report may not work properly because ONLY_FULL_GROUP_BY is enabled, consult with your hosting provider to disable ONLY_FULL_GROUP_BY in sql_mode configuration. In case the items report is working properly you can just ignore this message.
    </div>
  <?php } ?>
  <p class="mbot20 text-info"><?php echo _l('item_report_paid_invoices_notice'); ?></p>
  <?php if(1 ) { ?>
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
<?php } ?>
<table class="table table-items-report scroll-responsive">
  <thead>
    <tr>
      <th><?php echo _l('reports_item'); ?></th>
      <th><?php echo _l('Date'); ?></th>
      
      <th><?php echo _l('Customer'); ?></th>
      <th><?php echo _l('Invoice'); ?></th>
      <th><?php echo _l('quantity_sold'); ?></th>
      <th><?php echo _l('total_amount'); ?></th>
      <th><?php echo _l('price per Item'); ?></th>
    </tr>
  </thead>
  <tbody>

  </tbody>
  <tfoot>
    <tr>
      <td></td>
      <td class="qty"></td>
      <td class="amount"></td>
      <td></td>
    </tr>
  </tfoot>
</table>
</div>
