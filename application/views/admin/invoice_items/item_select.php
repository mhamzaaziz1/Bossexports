<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="form-group mbot25 items-wrapper select-placeholder<?php if(has_permission('items','','create')){ echo ' input-group-select'; } ?>">
    <?php //var_dump( $invoice->clientid) ?>
  <div class="<?php if(has_permission('items','','create')){ echo 'input-group input-group-select'; } ?>">
    <div class="items-select-wrapper">
     <select name="item_select" class="selectpicker no-margin<?php if(isset($ajaxItems) && $ajaxItems == true){echo ' ajax-search';} ?><?php if(has_permission('items','','create')){ echo ' _select_input_group'; } ?>" data-width="false" id="item_select" data-none-selected-text="<?php echo _l('add_item'); ?>" data-live-search="true">
      <option value=""></option>
      <?php if(isset($items) && is_array($items)) { foreach($items as $group_id=>$_items){ ?>
      <optgroup data-group-id="<?php echo $group_id; ?>" label="<?php echo $_items[0]['group_name']; ?>">
       <?php foreach($_items as $item){ ?>
       <?php 
       $last_price = '';
       // Check if we have a client ID from the invoice object
       $client_id = null;
       if(isset($invoice) && isset($invoice->clientid)) {
           $client_id = $invoice->clientid;
       } elseif(isset($clientid)) {
           $client_id = $clientid;
       }

       if($client_id) {
           $CI =& get_instance();
           $CI->load->model('invoice_items_model');
           $last_sale_price = $CI->invoice_items_model->get_last_sale_price($item['id'], $client_id);
           if($last_sale_price !== null) {
               $last_price = ' [Last Sale: ' . app_format_number($last_sale_price) . ']';
           }
       }
       ?>
       <option value="<?php echo $item['id']; ?>" data-subtext="<?php echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; ?>">(<?php echo app_format_number($item['rate']); ; ?>) <?php echo $item['description'] . $last_price; ?></option>
       <?php } ?>
     </optgroup>
     <?php } } ?>
   </select>
 </div>
 <?php if(has_permission('items','','create')){ ?>
 <div class="input-group-addon">
   <a href="#" data-toggle="modal" data-target="#sales_item_modal">
    <i class="fa fa-plus"></i>
  </a>
</div>
<?php } ?>
</div>
</div>
