<?php defined('BASEPATH') or exit('No direct script access allowed');
if($proforma->status == Proforma_model::STATUS_DRAFT){ ?>
   <div class="alert alert-info">
      <?php echo _l('invoice_draft_status_info'); ?>
   </div>
<?php } ?>

<div id="proforma-preview">
   <div class="row">
      <?php if($proforma->project_id != 0){ ?>
         <div class="col-md-12">
            <h4 class="font-medium mtop15 mbot20"><?php echo _l('related_to_project',array(
               _l('proforma_invoice_lowercase'),
               _l('project_lowercase'),
               '<a href="'.admin_url('projects/view/'.$proforma->project_id).'" target="_blank">' . get_project_name_by_id($proforma->project_id) . '</a>',
               )); ?></h4>
         </div>
      <?php } ?>
      <div class="col-md-6 col-sm-6">
         <h4 class="bold">
            <?php
            $tags = get_tags_in($proforma->id,'proforma');
            if(count($tags) > 0){
               echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="'.html_escape(implode(', ',$tags)).'"></i>';
            }
            ?>
            <a href="<?php echo admin_url('proforma/invoice/'.$proforma->id); ?>">
               <span id="proforma-number">
                  <?php echo format_proforma_number($proforma->id); ?>
               </span>
            </a>
         </h4>
         <address>
            <?php echo format_organization_info(); ?>
         </address>
      </div>
      <div class="col-sm-6 text-right">
         <span class="bold"><?php echo _l('invoice_bill_to'); ?>:</span>
         <address>
            <?php echo format_customer_info($proforma, 'invoice', 'billing', true); ?>
         </address>
         <?php if($proforma->include_shipping == 1 && $proforma->show_shipping_on_invoice == 1){ ?>
            <span class="bold"><?php echo _l('ship_to'); ?>:</span>
            <address>
               <?php echo format_customer_info($proforma, 'invoice', 'shipping'); ?>
            </address>
         <?php } ?>
         <p class="no-mbot">
            <span class="bold">
               <?php echo _l('invoice_data_date'); ?>
            </span>
            <?php echo _d($proforma->date); ?>
         </p>
         <?php if(!empty($proforma->duedate)){ ?>
            <p class="no-mbot">
               <span class="bold">
                  <?php echo _l('invoice_data_duedate'); ?>
               </span>
               <?php echo _d($proforma->duedate); ?>
            </p>
         <?php } ?>
         <?php if($proforma->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1){ ?>
            <p class="no-mbot">
               <span class="bold"><?php echo _l('sale_agent_string'); ?>: </span>
               <?php echo get_staff_full_name($proforma->sale_agent); ?>
            </p>
         <?php } ?>
         
         <?php $pdf_custom_fields = get_custom_fields('proforma',array('show_on_pdf'=>1)); // Using proforma type
         foreach($pdf_custom_fields as $field){
            $value = get_custom_field_value($proforma->id,$field['id'],'proforma');
            if($value == ''){continue;} ?>
            <p class="no-mbot">
               <span class="bold"><?php echo $field['name']; ?>: </span>
               <?php echo $value; ?>
            </p>
         <?php } ?>
      </div>
   </div>
   <div class="row">
      <div class="col-md-12">
         <div class="table-responsive">
            <table class="table items items-preview invoice-items-preview" data-type="invoice">
                <?php
                    $items_data = get_items_table_data($proforma, 'proforma', 'html', true); 
                    echo $items_data->table(); 
                ?>
            </table>
         </div>
      </div>
      <div class="col-md-5 col-md-offset-7">
         <table class="table text-right">
            <tbody>
               <tr id="subtotal">
                  <td><span class="bold"><?php echo _l('invoice_subtotal'); ?></span>
                  </td>
                  <td class="subtotal">
                     <?php echo app_format_money($proforma->subtotal, $proforma->currency_name ?? ''); ?>
                  </td>
               </tr>
               <?php if(is_sale_discount_applied($proforma)){ ?>
                  <tr>
                     <td>
                        <span class="bold"><?php echo _l('invoice_discount'); ?>
                        <?php if(is_sale_discount($proforma,'percent')){ ?>
                           (<?php echo app_format_number($proforma->discount_percent,true); ?>%)
                           <?php } ?></span>
                        </td>
                        <td class="discount">
                           <?php echo '-' . app_format_money($proforma->discount_total, $proforma->currency_name ?? ''); ?>
                        </td>
                     </tr>
                  <?php } ?>
                  <?php
                  // Taxes
                  $taxes = $items_data->taxes();
                  foreach ($taxes as $tax) {
                       echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('.app_format_number($tax['taxrate']).'%)</td><td>'.app_format_money($tax['total_tax'], $proforma->currency_name ?? '').'</td></tr>';
                 }
                 ?>
                 <?php if((int)$proforma->adjustment != 0){ ?>
                  <tr>
                     <td>
                        <span class="bold"><?php echo _l('invoice_adjustment'); ?></span>
                     </td>
                     <td class="adjustment">
                        <?php echo app_format_money($proforma->adjustment, $proforma->currency_name ?? ''); ?>
                     </td>
                  </tr>
               <?php } ?>
               <tr>
                  <td><span class="bold"><?php echo _l('invoice_total'); ?></span>
                  </td>
                  <td class="total">
                     <?php echo app_format_money($proforma->total, $proforma->currency_name ?? ''); ?>
                  </td>
               </tr>
               <?php if(count($proforma->payments) > 0) { ?>
                  <tr>
                     <td><span class="bold"><?php echo _l('invoice_total_paid'); ?></span></td>
                     <td>
                        <?php echo '-' . app_format_money(sum_from_table(db_prefix().'proforma_invoice_payment_records',array('field'=>'amount','where'=>array('proforma_invoice_id'=>$proforma->id))), $proforma->currency_name ?? ''); ?>
                     </td>
                  </tr>
                  <tr>
                      <td><span class="bold text-danger"><?php echo _l('invoice_amount_due'); ?></span></td>
                      <td>
                          <span class="text-danger">
                              <?php echo app_format_money($proforma->total - sum_from_table(db_prefix().'proforma_invoice_payment_records',array('field'=>'amount','where'=>array('proforma_invoice_id'=>$proforma->id))), $proforma->currency_name ?? ''); ?>
                          </span>
                      </td>
                  </tr>
               <?php } ?>
            </tbody>
         </table>
      </div>
   </div>
   <hr />
   <?php if(isset($proforma->clientnote) && $proforma->clientnote != ''){ ?>
      <div class="col-md-12 row mtop15">
         <p class="bold text-muted"><?php echo _l('invoice_note'); ?></p>
         <p><?php echo $proforma->clientnote; ?></p>
      </div>
   <?php } ?>
   <?php if(isset($proforma->adminnote) && $proforma->adminnote != ''){ ?>
      <div class="col-md-12 row mtop15">
         <p class="bold text-muted"><?php echo _l('invoice_admin_note'); ?></p>
         <p><?php echo $proforma->adminnote; ?></p>
      </div>
   <?php } ?>
   <?php if(isset($proforma->terms) && $proforma->terms != ''){ ?>
      <div class="col-md-12 row mtop15">
         <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
         <p><?php echo $proforma->terms; ?></p>
      </div>
   <?php } ?>
   <?php if(count($proforma->payments) > 0){ ?>
   <div class="row">
        <div class="col-md-12 mtop15">
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
   <?php } ?>
</div>
