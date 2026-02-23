<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="panel_s invoice accounting-template">
   <div class="additional"></div>
   <div class="panel-body">
      <?php if(isset($proforma)){ ?>
      <?php  echo format_invoice_status($proforma->status); ?>
      <hr class="hr-panel-heading" />
      <?php } ?>
      <?php hooks()->do_action('before_render_proforma_template'); ?>
      <?php if(isset($proforma)){
        echo form_hidden('merge_current_invoice',$proforma->id);
      } ?>
      <div class="row">
         <div class="col-md-6">
            <div class="f_client_id">
              <div class="form-group select-placeholder">
                <label for="clientid" class="control-label"><?php echo _l('invoice_select_customer'); ?></label>
                <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search<?php if(isset($proforma) && empty($proforma->clientid)){echo ' customer-removed';} ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
               <?php $selected = (isset($proforma) ? $proforma->clientid : '');
                 if($selected == ''){
                   $selected = (isset($customer_id) ? $customer_id: '');
                 }
                 if($selected != ''){
                    $rel_data = get_relation_data('customer',$selected);
                    $rel_val = get_relation_values($rel_data,'customer');
                    echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
                 } ?>
                </select>
              </div>
            </div>
            
            <div class="row">
               <div class="col-md-12">
               <hr class="hr-10" />
                  <a href="#" class="edit_shipping_billing_info" data-toggle="modal" data-target="#billing_and_shipping_details"><i class="fa fa-pencil-square-o"></i></a>
                  <?php include_once(APPPATH .'views/admin/invoices/billing_and_shipping_template.php'); ?>
               </div>
               <div class="col-md-6">
                  <p class="bold"><?php echo _l('invoice_bill_to'); ?></p>
                  <address>
                     <span class="billing_street">
                     <?php $billing_street = (isset($proforma) ? $proforma->billing_street : '--'); ?>
                     <?php $billing_street = ($billing_street == '' ? '--' :$billing_street); ?>
                     <?php echo $billing_street; ?></span><br>
                     <span class="billing_city">
                     <?php $billing_city = (isset($proforma) ? $proforma->billing_city : '--'); ?>
                     <?php $billing_city = ($billing_city == '' ? '--' :$billing_city); ?>
                     <?php echo $billing_city; ?></span>,
                     <span class="billing_state">
                     <?php $billing_state = (isset($proforma) ? $proforma->billing_state : '--'); ?>
                     <?php $billing_state = ($billing_state == '' ? '--' :$billing_state); ?>
                     <?php echo $billing_state; ?></span>
                     <br/>
                     <span class="billing_country">
                     <?php $billing_country = (isset($proforma) ? get_country_short_name($proforma->billing_country) : '--'); ?>
                     <?php $billing_country = ($billing_country == '' ? '--' :$billing_country); ?>
                     <?php echo $billing_country; ?></span>,
                     <span class="billing_zip">
                     <?php $billing_zip = (isset($proforma) ? $proforma->billing_zip : '--'); ?>
                     <?php $billing_zip = ($billing_zip == '' ? '--' :$billing_zip); ?>
                     <?php echo $billing_zip; ?></span>
                  </address>
               </div>
               <div class="col-md-6">
                  <p class="bold"><?php echo _l('ship_to'); ?></p>
                  <address>
                     <span class="shipping_street">
                     <?php $shipping_street = (isset($proforma) ? $proforma->shipping_street : '--'); ?>
                     <?php $shipping_street = ($shipping_street == '' ? '--' :$shipping_street); ?>
                     <?php echo $shipping_street; ?></span><br>
                     <span class="shipping_city">
                     <?php $shipping_city = (isset($proforma) ? $proforma->shipping_city : '--'); ?>
                     <?php $shipping_city = ($shipping_city == '' ? '--' :$shipping_city); ?>
                     <?php echo $shipping_city; ?></span>,
                     <span class="shipping_state">
                     <?php $shipping_state = (isset($proforma) ? $proforma->shipping_state : '--'); ?>
                     <?php $shipping_state = ($shipping_state == '' ? '--' :$shipping_state); ?>
                     <?php echo $shipping_state; ?></span>
                     <br/>
                     <span class="shipping_country">
                     <?php $shipping_country = (isset($proforma) ? get_country_short_name($proforma->shipping_country) : '--'); ?>
                     <?php $shipping_country = ($shipping_country == '' ? '--' :$shipping_country); ?>
                     <?php echo $shipping_country; ?></span>,
                     <span class="shipping_zip">
                     <?php $shipping_zip = (isset($proforma) ? $proforma->shipping_zip : '--'); ?>
                     <?php $shipping_zip = ($shipping_zip == '' ? '--' :$shipping_zip); ?>
                     <?php echo $shipping_zip; ?></span>
                  </address>
               </div>
            </div>
            <?php
               $next_invoice_number = get_option('next_proforma_invoice_number');
               $format = get_option('invoice_number_format');

               if(isset($proforma)){
                  $format = $proforma->number_format;
               }

               $prefix = get_option('proforma_number_prefix');

               if ($format == 1) {
                 $__number = $next_invoice_number;
                 if(isset($proforma)){
                   $__number = $proforma->number;
                   $prefix = '<span id="prefix">' . $proforma->prefix . '</span>';
                 }
               } else if($format == 2) {
                 if(isset($proforma)){
                   $__number = $proforma->number;
                   $prefix = $proforma->prefix;
                   $prefix = '<span id="prefix">'. $prefix . '</span><span id="prefix_year">' .date('Y',strtotime($proforma->date)).'</span>/';
                 } else {
                  $__number = $next_invoice_number;
                  $prefix = $prefix.'<span id="prefix_year">'.date('Y').'</span>/';
                }
               } else if($format == 3) {
                  if(isset($proforma)){
                   $yy = date('y',strtotime($proforma->date));
                   $__number = $proforma->number;
                   $prefix = '<span id="prefix">'. $proforma->prefix . '</span>';
                 } else {
                  $yy = date('y');
                  $__number = $next_invoice_number;
                }
               } else if($format == 4) {
                  if(isset($proforma)){
                   $yyyy = date('Y',strtotime($proforma->date));
                   $mm = date('m',strtotime($proforma->date));
                   $__number = $proforma->number;
                   $prefix = '<span id="prefix">'. $proforma->prefix . '</span>';
                 } else {
                  $yyyy = date('Y');
                  $mm = date('m');
                  $__number = $next_invoice_number;
                }
               }

               $_is_draft = (isset($proforma) && $proforma->status == 6) ? true : false; // 6 is draft normally
               $_invoice_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
               $isedit = isset($proforma) ? 'true' : 'false';
               $data_original_number = isset($proforma) ? $proforma->number : 'false';

               ?>
            <div class="form-group">
               <label for="number">
                  <?php echo _l('proforma_invoice_number'); ?> 
                  <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('invoice_number_not_applied_on_draft') ?>" data-placement="top"></i>
            </label>
               <div class="input-group">
                  <span class="input-group-addon">
                  <?php if(isset($proforma)){ ?>
                     <!-- Settings cog removed for proforma -->
                  <?php }
                    echo $prefix;
                  ?>
                  </span>
                  <input type="text" name="number" class="form-control" value="<?php echo ($_is_draft) ? 'DRAFT' : $_invoice_number; ?>" data-isedit="<?php echo $isedit; ?>" data-original-number="<?php echo $data_original_number; ?>" <?php echo ($_is_draft) ? 'disabled' : '' ?>>
                  <?php if($format == 3) { ?>
                  <span class="input-group-addon">
                     <span id="prefix_year" class="format-n-yy"><?php echo $yy; ?></span>
                  </span>
                  <?php } else if($format == 4) { ?>
                   <span class="input-group-addon">
                     <span id="prefix_month" class="format-mm-yyyy"><?php echo $mm; ?></span>
                     /
                     <span id="prefix_year" class="format-mm-yyyy"><?php echo $yyyy; ?></span>
                  </span>
                  <?php } ?>
               </div>
            </div>
            <div class="row">
               <div class="col-md-6">
                  <?php $value = (isset($proforma) ? _d($proforma->date) : _d(date('Y-m-d')));
                  $date_attrs = array();
                  ?>
                  <?php echo render_date_input('date','invoice_add_edit_date',$value,$date_attrs); ?>
               </div>
               <div class="col-md-6">
                  <?php
                  $value = '';
                  if(isset($proforma)){
                    $value = _d($proforma->duedate);
                  } else {
                    if(get_option('invoice_due_after') != 0){
                        $value = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                    }
                  }
                   ?>
                  <?php echo render_date_input('duedate','invoice_add_edit_duedate',$value); ?>
               </div>
            </div>
               
               <?php $rel_id = (isset($proforma) ? $proforma->id : false); ?>
               <?php echo render_custom_fields('invoice',$rel_id); ?>
         </div>
         <div class="col-md-6">
            <div class="panel_s no-shadow">

                   <div class="form-group">
                  <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                  <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($proforma) ? prep_tags_input(get_tags_in($proforma->id,'proforma')) : ''); ?>" data-role="tagsinput">
               </div>
               <div class="form-group mbot15 select-placeholder">
                  <label for="allowed_payment_modes" class="control-label"><?php echo _l('invoice_add_edit_allowed_payment_modes'); ?></label>
                  <br />
                  <?php if(count($payment_modes) > 0){ ?>
                  <select class="selectpicker"
                  data-toggle="<?php echo $this->input->get('allowed_payment_modes'); ?>"
                  name="allowed_payment_modes[]"
                  data-actions-box="true"
                  multiple="true"
                  data-width="100%"
                  data-title="<?php echo _l('dropdown_non_selected_tex'); ?>">
                  <?php foreach($payment_modes as $mode){
                     $selected = '';
                     if(isset($proforma)){
                       if($proforma->allowed_payment_modes){
                        $inv_modes = unserialize($proforma->allowed_payment_modes);
                        if(is_array($inv_modes)) {
                         foreach($inv_modes as $_allowed_payment_mode){
                           if($_allowed_payment_mode == $mode['id']){
                             $selected = ' selected';
                           }
                         }
                       }
                     }
                     } else {
                     if($mode['selected_by_default'] == 1){
                        $selected = ' selected';
                     }
                     }
                     ?>
                     <option value="<?php echo $mode['id']; ?>"<?php echo $selected; ?> selected><?php echo $mode['name']; ?></option>
                  <?php } ?>
                  </select>
                  <?php } else { ?>
                  <p><?php echo _l('invoice_add_edit_no_payment_modes_found'); ?></p>
                  <a class="btn btn-info" href="<?php echo admin_url('paymentmodes'); ?>">
                  <?php echo _l('new_payment_mode'); ?>
                  </a>
                  <?php } ?>
               </div>

               <div class="row">
                  <div class="col-md-6">
                     <?php
                        $currency_attr = array('disabled'=>true,'data-show-subtext'=>true);
                        $currency_attr = apply_filters_deprecated('invoice_currency_disabled', [$currency_attr], '2.3.0', 'invoice_currency_attributes');

                        foreach($currencies as $currency){
                         if($currency['isdefault'] == 1){
                           $currency_attr['data-base'] = $currency['id'];
                         }
                         if(isset($proforma)){
                          if($currency['id'] == $proforma->currency){
                           $selected = $currency['id'];
                         }
                        } else {
                         if($currency['isdefault'] == 1){
                           $selected = $currency['id'];
                         }
                        }
                        }
                        $currency_attr = hooks()->apply_filters('invoice_currency_attributes',$currency_attr);
                        ?>
                     <?php echo render_select('currency', $currencies, array('id','name','symbol'), 'invoice_add_edit_currency', $selected, $currency_attr); ?>
                  </div>
                  <div class="col-md-6">
                     <?php
                        $i = 0;
                        $selected = '';
                        foreach($staff as $member){
                         if(isset($proforma)){
                           if($proforma->sale_agent == $member['staffid']) {
                             $selected = $member['staffid'];
                           }
                         }
                         $i++;
                        }
                        echo render_select('sale_agent',$staff,array('staffid',array('firstname','lastname')),'sale_agent_string',$selected);
                        ?>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group select-placeholder">
                        <label for="discount_type" class="control-label"><?php echo _l('discount_type'); ?></label>
                        <select name="discount_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value="" selected><?php echo _l('no_discount'); ?></option>
                           <option value="before_tax" <?php
                              if(isset($proforma)){ if($proforma->discount_type == 'before_tax'){ echo 'selected'; }} ?>><?php echo _l('discount_type_before_tax'); ?></option>
                           <option value="after_tax" <?php if(isset($proforma)){if($proforma->discount_type == 'after_tax'){echo 'selected';}} ?>><?php echo _l('discount_type_after_tax'); ?></option>
                        </select>
                     </div>
                  </div>
               </div>
               <?php $value = (isset($proforma) ? $proforma->adminnote : ''); ?>
               <?php echo render_textarea('adminnote','invoice_add_edit_admin_note',$value); ?>

            </div>
         </div>
      </div>
   </div>
   <div class="panel-body mtop10">
      <div class="row">
         <div class="col-md-4">
            <?php $this->load->view('admin/invoice_items/item_select'); ?>
         </div>
         <div class="col-md-8 text-right show_quantity_as_wrapper">
            <div class="mtop10">
               <span><?php echo _l('show_quantity_as'); ?> </span>
               <div class="radio radio-primary radio-inline">
                  <input type="radio" value="1" id="sq_1" name="show_quantity_as" data-text="<?php echo _l('invoice_table_quantity_heading'); ?>" <?php if(isset($proforma) && $proforma->show_quantity_as == 1){echo 'checked';}else if(isset($proforma) && $proforma->show_quantity_as == 0){echo'checked';} ?>>
                  <label for="sq_1"><?php echo _l('quantity_as_qty'); ?></label>
               </div>
               <div class="radio radio-primary radio-inline">
                  <input type="radio" value="2" id="sq_2" name="show_quantity_as" data-text="<?php echo _l('invoice_table_hours_heading'); ?>" <?php if(isset($proforma) && $proforma->show_quantity_as == 2){echo 'checked';} ?>>
                  <label for="sq_2"><?php echo _l('quantity_as_hours'); ?></label>
               </div>
               <div class="radio radio-primary radio-inline">
                  <input type="radio" value="3" id="sq_3" name="show_quantity_as" data-text="<?php echo _l('invoice_table_quantity_heading'); ?>/<?php echo _l('invoice_table_hours_heading'); ?>" <?php if(isset($proforma) && $proforma->show_quantity_as == 3){echo 'checked';} ?>>
                  <label for="sq_3"><?php echo _l('invoice_table_quantity_heading'); ?>/<?php echo _l('invoice_table_hours_heading'); ?></label>
               </div>
            </div>
         </div>
      </div>
      <div class="table-responsive s_table">
         <table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
            <thead>
               <tr>
                  <th></th>
                  <th width="20%" align="left"><i class="fa fa-exclamation-circle" aria-hidden="true" data-toggle="tooltip" data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> <?php echo _l('invoice_table_item_heading'); ?></th>
                  <th width="25%" align="left"><?php echo _l('invoice_table_item_description'); ?></th>
                  <?php
                  $custom_fields = get_custom_fields('items');
                  foreach($custom_fields as $cf){
                    echo '<th width="15%" align="left" class="custom_field">' . $cf['name'] . '</th>';
                  }
                     $qty_heading = _l('invoice_table_quantity_heading');
                     if(isset($proforma) && $proforma->show_quantity_as == 2){
                      $qty_heading = _l('invoice_table_hours_heading');
                     } else if(isset($proforma) && $proforma->show_quantity_as == 3){
                      $qty_heading = _l('invoice_table_quantity_heading') .'/'._l('invoice_table_hours_heading');
                     }
                     ?>
                  <th width="10%" align="right" class="qty"><?php echo $qty_heading; ?></th>
                  <th width="15%" align="right"><?php echo _l('invoice_table_rate_heading'); ?></th>
                  <th width="20%" align="right"><?php echo _l('invoice_table_tax_heading'); ?></th>
                  <th width="10%" align="right"><?php echo _l('invoice_table_amount_heading'); ?></th>
                  <th align="center"><i class="fa fa-cog"></i></th>
               </tr>
            </thead>
            <tbody>
               <tr class="main">
                  <td></td>
                  <td>
                     <textarea name="description" class="form-control" rows="4" placeholder="<?php echo _l('item_description_placeholder'); ?>"></textarea>
                  </td>
                  <td>
                     <textarea name="long_description" rows="4" class="form-control" placeholder="<?php echo _l('item_long_description_placeholder'); ?>"></textarea>
                  </td>
                  <?php echo render_custom_fields_items_table_add_edit_preview(); ?>
                  <td>
                     <input type="number" name="quantity" min="0" value="1" class="form-control" placeholder="<?php echo _l('item_quantity_placeholder'); ?>">
                     <input type="text" placeholder="<?php echo _l('unit'); ?>" data-toggle="tooltip" data-title="e.q kg, lots, packs" name="unit" class="form-control input-transparent text-right">
                  </td>
                  <td>
                     <input type="number" name="rate" class="form-control" placeholder="<?php echo _l('item_rate_placeholder'); ?>">
                  </td>
                  <td>
                     <?php
                        $default_tax = unserialize(get_option('default_tax'));
                        $select = '<select class="selectpicker display-block tax main-tax" data-width="100%" name="taxname" multiple data-none-selected-text="'._l('no_tax').'">';
                      //  $select .= '<option value=""'.(count($default_tax) == 0 ? ' selected' : '').'>'._l('no_tax').'</option>';
                        foreach($taxes as $tax){
                        $selected = '';
                         if(is_array($default_tax)){
                             if(in_array($tax['name'] . '|' . $tax['taxrate'],$default_tax)){
                                  $selected = ' selected ';
                             }
                        }
                        $select .= '<option value="'.$tax['name'].'|'.$tax['taxrate'].'"'.$selected.'data-taxrate="'.$tax['taxrate'].'" data-taxname="'.$tax['name'].'" data-subtext="'.$tax['name'].'">'.$tax['taxrate'].'%</option>';
                        }
                        $select .= '</select>';
                        echo $select;
                        ?>
                  </td>
                  <td></td>
                  <td>
                     <?php
                        $new_item = 'undefined';
                        if(isset($proforma)){
                         $new_item = true;
                        }
                        ?>
                     <button type="button" onclick="add_item_to_table('undefined','undefined',<?php echo $new_item; ?>); return false;" class="btn pull-right btn-info"><i class="fa fa-check"></i></button>
                  </td>
               </tr>
               <?php if (isset($proforma) || isset($add_items)) {
                  $i               = 1;
                  $items_indicator = 'newitems';
                  if (isset($proforma)) {
                    $add_items       = $proforma->items;
                    $items_indicator = 'items';
                  }
                  foreach ($add_items as $item) {

                    $manual    = false;
                    $table_row = '<tr class="sortable item">';
                    $table_row .= '<td class="dragger">';
                    if (!is_numeric($item['qty'])) {
                      $item['qty'] = 1;
                    }
                    $invoice_item_taxes = get_proforma_item_taxes($item['id']);
                    // passed like string
                    if ($item['id'] == 0) {
                        $invoice_item_taxes = $item['taxname'];
                        $manual             = true;
                    }
                    $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][itemid]', $item['id']);
                    $amount = $item['rate'] * $item['qty'];
                    $amount = app_format_number($amount);
                    // order input
                    $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]">';
                    $table_row .= '</td>';
                    $table_row .= '<td class="bold description"><textarea name="' . $items_indicator . '[' . $i . '][description]" class="form-control" rows="5">' . clear_textarea_breaks($item['description']) . '</textarea></td>';
                    $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][long_description]" class="form-control" rows="5">' . clear_textarea_breaks($item['long_description']) . '</textarea></td>';

                    $table_row .= render_custom_fields_items_table_in($item,$items_indicator.'['.$i.']');

                    $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="' . $items_indicator . '[' . $i . '][qty]" value="' . $item['qty'] . '" class="form-control" readonly>';

                    $unit_placeholder = '';
                    if(!$item['unit']){
                      $unit_placeholder = _l('unit');
                      $item['unit'] = '';
                    }

                    $table_row .= '<input type="text" placeholder="'.$unit_placeholder.'" name="'.$items_indicator.'['.$i.'][unit]" class="form-control input-transparent text-right" value="'.$item['unit'].'">';

                    $table_row .= '</td>';
                    $table_row .= '<td class="rate"><input type="number" data-toggle="tooltip" title="' . _l('numbers_not_formatted_while_editing') . '" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][rate]" value="' . $item['rate'] . '" class="form-control" readonly></td>';
                    $table_row .= '<td class="taxrate">' . $this->misc_model->get_taxes_dropdown_template('' . $items_indicator . '[' . $i . '][taxname][]', $invoice_item_taxes, 'invoice', $item['id'], true, $manual) . '</td>';
                    $table_row .= '<td class="amount" align="right">' . $amount . '</td>';
                    $table_row .= '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' . $item['id'] . '); return false;"><i class="fa fa-times"></i></a></td>';
                    $table_row .= '</tr>';
                    echo $table_row;
                    $i++;
                  }
                  }
                  ?>
            </tbody>
         </table>
      </div>
      <div class="col-md-8 col-md-offset-4">
       <button type="button" id="caldata" class="btn pull-right btn-info">Calculate Values</button>
         <table class="table text-right">
            <tbody>
               <tr id="subtotal">
                  <td><span class="bold"><?php echo _l('invoice_subtotal'); ?> :</span>
                  </td>
                  <td class="subtotal">
                  </td>
               </tr>
               <tr id="discount_area">
                  <td>
                     <div class="row">
                        <div class="col-md-7">
                           <span class="bold">
                            <?php echo _l('invoice_discount'); ?>
                         </span>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group" id="discount-total">

                                <input type="number" value="<?php echo (isset($proforma) ? $proforma->discount_percent : 0); ?>" class="form-control pull-left input-discount-percent<?php if(isset($proforma) && !is_sale_discount($proforma,'percent') && is_sale_discount_applied($proforma)){echo ' hide';} ?>" min="0" max="100" name="discount_percent">

                                <input type="number" data-toggle="tooltip" data-title="<?php echo _l('numbers_not_formatted_while_editing'); ?>" value="<?php echo (isset($proforma) ? $proforma->discount_total : 0); ?>" class="form-control pull-left input-discount-fixed<?php if(!isset($proforma) || (isset($proforma) && !is_sale_discount($proforma,'fixed'))){echo ' hide';} ?>" min="0" name="discount_total">

                                <div class="input-group-addon">
                                  <div class="dropdown">
                                    <a class="dropdown-toggle" href="#" id="dropdown_menu_tax_total_type" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                      <span class="discount-total-type-selected">
                                        <?php if(!isset($proforma) || isset($proforma) && (is_sale_discount($proforma,'percent') || !is_sale_discount_applied($proforma))) {
                                          echo '%';
                                        } else {
                                          echo _l('discount_fixed_amount');
                                        }
                                        ?>
                                      </span>
                                      <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu" id="discount-total-type-dropdown" aria-labelledby="dropdown_menu_tax_total_type">
                                      <li>
                                        <a href="#" class="discount-total-type discount-type-percent<?php if(!isset($proforma) || (isset($proforma) && is_sale_discount($proforma,'percent')) || (isset($proforma) && !is_sale_discount_applied($proforma))){echo ' selected';} ?>">%</a>
                                      </li>
                                      <li><a href="#" class="discount-total-type discount-type-fixed<?php if(isset($proforma) && is_sale_discount($proforma,'fixed')){echo ' selected';} ?>">
                                          <?php echo _l('discount_fixed_amount'); ?>
                                        </a>
                                      </li>
                                    </ul>
                                  </div>
                                </div>
                            </div>
                        </div>
                     </div>
                  </td>
                  <td class="discount-total"></td>
               </tr>
               <tr>
                  <td><span class="bold"><?php echo _l('invoice_total'); ?> :</span>
                  </td>
                  <td class="total">
                  </td>
               </tr>
            </tbody>
         </table>
      </div>
      <div id="removed-items"></div>
    <div id="removed-items"></div>
   </div>
   <?php if(isset($proforma) && count($proforma->payments) > 0){ ?>
   <div class="panel-body mtop10">
   <div class="row">
        <div class="col-md-12">
        <h4 class="bold"><?php echo _l('payments'); ?></h4>
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="bold"><?php echo _l('payments_table_date_heading'); ?></th>
                        <th class="bold"><?php echo _l('payments_table_mode_heading'); ?></th>
                        <th class="bold"><?php echo _l('payments_table_transaction_id_heading'); ?></th>
                        <th class="bold"><?php echo _l('payments_table_amount_heading'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($proforma->payments as $payment){ ?>
                        <tr>
                            <td><?php echo _d($payment['date']); ?></td>
                            <td><?php echo $payment['name'] ?? $payment['paymentmode']; ?></td>
                            <td><?php echo $payment['transactionid']; ?></td>
                            <td><?php echo app_format_money($payment['amount'], $proforma->currency_name ?? ''); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        </div>
   </div>
   </div>
   <?php } ?>
   <div class="row">
      <div class="col-md-12 mtop15">
         <div class="panel-body bottom-transaction">
            <?php $value = (isset($proforma) ? $proforma->clientnote : get_option('predefined_clientnote_invoice')); ?>
            <?php echo render_textarea('clientnote','invoice_add_edit_client_note',$value,array(),array(),'mtop15'); ?>
            <?php $value = (isset($proforma) ? $proforma->terms : get_option('predefined_terms_invoice')); ?>
            <?php echo render_textarea('terms','terms_and_conditions',$value,array(),array(),'mtop15'); ?>
            <div class="btn-bottom-toolbar text-right">
                <?php if(!isset($proforma)){ ?>
                <button class="btn-tr btn btn-default mleft10 text-right invoice-form-submit save-as-draft transaction-submit">
                <?php echo _l('save_as_draft'); ?>
                </button>
                <?php } ?>
              <div class="btn-group dropup">
                <button type="button" class="btn-tr btn btn-info invoice-form-submit transaction-submit"><?php echo _l('submit'); ?></button>
                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right width200">
                  <li>
                    <a href="#" class="invoice-form-submit save-and-send transaction-submit">
                      <?php echo _l('save_and_send'); ?>
                    </a>
                  </li>
                  <?php if(!isset($proforma)) { ?>
                  <li>
                    <a href="#" class="invoice-form-submit save-and-send-later transaction-submit">
                  <?php echo _l('save_and_send_later'); ?>
                    </a>
                  </li>
                  <?php } ?>
                </ul>
              </div>
             </div>
         </div>
        <div class="btn-bottom-pusher"></div>
      </div>
   </div>
</div>
