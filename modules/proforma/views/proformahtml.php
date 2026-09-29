<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $proforma->number; ?></title>
    <?php echo app_compile_css('admin'); ?>
    <?php echo app_compile_scripts('admin'); ?>
</head>
<body class="pdf-content">
<div id="wrapper">
    <div class="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                   <div class="panel_s">
                      <div class="panel-body">
                          <?php if($proforma->status == Proforma_model::STATUS_DRAFT){ ?>
                             <div class="alert alert-info">
                                <?php echo _l('invoice_draft_status_info'); ?>
                             </div>
                          <?php } ?>
                          <div class="row">
                              <div class="col-md-6 col-sm-6 transaction-html-info-col-left">
                                  <h4 class="bold proforma-html-number"><?php echo format_proforma_number($proforma->id); ?></h4>
                                  <address class="proforma-html-company-info">
                                      <?php echo format_organization_info(); ?>
                                  </address>
                              </div>
                              <div class="col-sm-6 text-right transaction-html-info-col-right">
                                  <span class="bold proforma-html-bill-to"><?php echo _l('invoice_bill_to'); ?>:</span>
                                  <address class="proforma-html-customer-billing-info">
                                      <?php echo format_customer_info($proforma, 'invoice', 'billing'); ?>
                                  </address>
                                  <?php if($proforma->include_shipping == 1 && $proforma->show_shipping_on_invoice == 1){ ?>
                                      <span class="bold"><?php echo _l('ship_to'); ?>:</span>
                                      <address class="proforma-html-customer-shipping-info">
                                          <?php echo format_customer_info($proforma, 'invoice', 'shipping'); ?>
                                      </address>
                                  <?php } ?>
                                  <p class="no-mbot">
                                      <span class="bold">
                                         <?php echo _l('invoice_data_date'); ?>:
                                      </span>
                                      <?php echo _d($proforma->date); ?>
                                  </p>
                                  <?php if(!empty($proforma->duedate)){ ?>
                                      <p class="no-mbot">
                                         <span class="bold">
                                            <?php echo _l('invoice_data_duedate'); ?>:
                                         </span>
                                         <?php echo _d($proforma->duedate); ?>
                                      </p>
                                  <?php } ?>
                              </div>
                          </div>
              
                          <div class="row mtop20">
                              <div class="col-md-12">
                                  <div class="table-responsive">
                                       <table class="table items items-preview invoice-items-preview" data-type="invoice">
                                           <?php 
                                           $items_data = get_items_table_data($proforma, 'proforma'); 
                                           echo $items_data->table(); 
                                           ?>
                                       </table>
                                  </div>
                              </div>
              
                              <div class="col-md-6 col-md-offset-6">
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
                                                 <td><span class="bold"><?php echo _l('invoice_discount'); ?>
                                                 <?php if(is_sale_discount($proforma,'percent')){ ?>
                                                 (<?php echo app_format_number($proforma->discount_percent,true); ?>%)
                                                 <?php } ?></span></td>
                                                 <td class="discount">
                                                     -<?php echo app_format_money($proforma->discount_total, $proforma->currency_name ?? ''); ?>
                                                 </td>
                                             </tr>
                                          <?php } ?>
                                          <?php foreach ($items_data->taxes() as $tax) { ?>
                                          <tr>
                                              <td><span class="bold"><?php echo $tax['taxname']; ?> (<?php echo app_format_number($tax['taxrate']); ?>%)</span></td>
                                              <td><?php echo app_format_money($tax['total_tax'], $proforma->currency_name ?? ''); ?></td>
                                          </tr>
                                          <?php } ?>
                                          
                                          <tr>
                                              <td><span class="bold"><?php echo _l('invoice_total'); ?></span>
                                              </td>
                                              <td class="total">
                                                  <?php echo app_format_money($proforma->total, $proforma->currency_name ?? ''); ?>
                                              </td>
                                          </tr>
                                           <?php if(count($proforma->payments) > 0){ ?>
                                              <tr>
                                                  <td><span class="bold"><?php echo _l('invoice_total_paid'); ?></span></td>
                                                  <td>
                                                      -<?php echo app_format_money(sum_from_table(db_prefix().'proforma_invoice_payment_records',array('field'=>'amount','where'=>array('proforma_invoice_id'=>$proforma->id))), $proforma->currency_name ?? ''); ?>
                                                  </td>
                                              </tr>
                                          <?php } ?>
                                      </tbody>
                                  </table>
                              </div>
                              
                              <?php if(isset($proforma->clientnote) && $proforma->clientnote != ''){ ?>
                                 <div class="col-md-12 row mtop15">
                                    <p class="bold text-muted"><?php echo _l('invoice_note'); ?></p>
                                    <p><?php echo $proforma->clientnote; ?></p>
                                 </div>
                              <?php } ?>
                              <?php if(isset($proforma->terms) && $proforma->terms != ''){ ?>
                                 <div class="col-md-12 row mtop15">
                                    <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
                                    <p><?php echo $proforma->terms; ?></p>
                                 </div>
                              <?php } ?>
                                  <?php if(count($proforma->payments) > 0){ ?>
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
                                  <?php } ?>
                          </div>
                      </div>
                   </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
