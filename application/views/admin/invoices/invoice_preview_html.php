<?php defined('BASEPATH') or exit('No direct script access allowed');
if($invoice->status == Invoices_model::STATUS_DRAFT){ ?>
   <div class="alert alert-info">
      <?php echo _l('invoice_draft_status_info'); ?>
   </div>
<?php }
if(isset($invoice->scheduled_email) && $invoice->scheduled_email) { ?>
   <div class="alert alert-warning">
      <?php echo _l('invoice_will_be_sent_at', _dt($invoice->scheduled_email->scheduled_at)); ?>
      <?php if(staff_can('edit', 'invoices') || $invoice->addedfrom == get_staff_user_id()) { ?>
         <a href="#"
         onclick="edit_invoice_scheduled_email(<?php echo $invoice->scheduled_email->id; ?>); return false;">
            <?php echo _l('edit'); ?>
         </a>
      <?php } ?>
   </div>
<?php } ?>
<div id="invoice-preview">
   <div class="row">
      <?php

      if($invoice->recurring > 0 || $invoice->is_recurring_from != NULL) {

         $recurring_invoice = $invoice;
         $show_recurring_invoice_info = true;

         if($invoice->is_recurring_from != NULL){
           $recurring_invoice = $this->invoices_model->get($invoice->is_recurring_from);
          // Maybe recurring invoice not longer recurring?
           if($recurring_invoice->recurring == 0){
            $show_recurring_invoice_info = false;
         } else {
            $next_recurring_date_compare = $recurring_invoice->last_recurring_date;
         }
      } else {
       $next_recurring_date_compare = $recurring_invoice->date;
       if($recurring_invoice->last_recurring_date){
        $next_recurring_date_compare = $recurring_invoice->last_recurring_date;
     }
  }
  if($show_recurring_invoice_info){
    if ($recurring_invoice->custom_recurring == 0) {
      $recurring_invoice->recurring_type = 'MONTH';
   }
   $next_date = date('Y-m-d', strtotime('+' . $recurring_invoice->recurring . ' ' . strtoupper($recurring_invoice->recurring_type), strtotime($next_recurring_date_compare)));
}
?>
<div class="col-md-12">
   <div class="mbot10">
      <?php if($invoice->is_recurring_from == null
         && $recurring_invoice->cycles > 0
         && $recurring_invoice->cycles == $recurring_invoice->total_cycles) { ?>
            <div class="alert alert-info no-mbot">
               <?php echo _l('recurring_has_ended', _l('invoice_lowercase')); ?>
            </div>
         <?php } else if($show_recurring_invoice_info){ ?>
            <span class="label label-default padding-5">
               <?php
               if($recurring_invoice->status == Invoices_model::STATUS_DRAFT){
                  echo '<i class="fa fa-exclamation-circle fa-fw text-warning" data-toggle="tooltip" title="'._l('recurring_invoice_draft_notice').'"></i>';
               }
               echo _l('cycles_remaining'); ?>:
               <b>
                  <?php
                  echo $recurring_invoice->cycles == 0 ? _l('cycles_infinity') : $recurring_invoice->cycles - $recurring_invoice->total_cycles;
                  ?>
               </b>
            </span>
            <?php
            if($recurring_invoice->cycles == 0 || $recurring_invoice->cycles != $recurring_invoice->total_cycles){
               echo '<span class="label label-default padding-5 mleft5"><i class="fa fa-question-circle fa-fw" data-toggle="tooltip" data-title="'._l('recurring_recreate_hour_notice',_l('invoice')).'"></i> ' . _l('next_invoice_date','<b>'._d($next_date).'</b>') .'</span>';
            }
         }
         ?>
      </div>
      <?php if($invoice->is_recurring_from != NULL){ ?>
         <?php echo '<p class="text-muted'.($show_recurring_invoice_info ? ' mtop15': '').'">'._l('invoice_recurring_from','<a href="'.admin_url('invoices/list_invoices/'.$invoice->is_recurring_from).'" onclick="init_invoice('.$invoice->is_recurring_from.');return false;">'.format_invoice_number($invoice->is_recurring_from).'</a></p>'); ?>
      <?php } ?>
   </div>
   <div class="clearfix"></div>
   <hr class="hr-10" />
<?php } ?>
<?php if($invoice->project_id != 0){ ?>
   <div class="col-md-12">
      <h4 class="font-medium mtop15 mbot20"><?php echo _l('related_to_project',array(
         _l('invoice_lowercase'),
         _l('project_lowercase'),
         '<a href="'.admin_url('projects/view/'.$invoice->project_id).'" target="_blank">' . $invoice->project_data->name . '</a>',
         )); ?></h4>
      </div>
   <?php } ?>
   <div class="col-md-6 col-sm-6">
      <h4 class="bold">
         <?php
         $tags = get_tags_in($invoice->id,'invoice');
         if(count($tags) > 0){
           echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="'.html_escape(implode(', ',$tags)).'"></i>';
        }
        ?>
        <a href="<?php echo admin_url('invoices/invoice/'.$invoice->id); ?>">
         <span id="invoice-number">
            <?php echo format_invoice_number($invoice->id); ?>
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
      <?php echo format_customer_info($invoice, 'invoice', 'billing', true); ?>
   </address>
   <?php if($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1){ ?>
      <span class="bold"><?php echo _l('ship_to'); ?>:</span>
      <address>
         <?php echo format_customer_info($invoice, 'invoice', 'shipping'); ?>
      </address>
   <?php } ?>
   <p class="no-mbot">
      <span class="bold">
         <?php echo _l('invoice_data_date'); ?>
      </span>
      <?php echo _d($invoice->date); ?>
   </p>
   <?php if(!empty($invoice->duedate)){ ?>
      <p class="no-mbot">
         <span class="bold">
            <?php echo _l('invoice_data_duedate'); ?>
         </span>
         <?php echo _d($invoice->duedate); ?>
      </p>
   <?php } ?>
   <?php if($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1){ ?>
      <p class="no-mbot">
         <span class="bold"><?php echo _l('sale_agent_string'); ?>: </span>
         <?php echo get_staff_full_name($invoice->sale_agent); ?>
      </p>
   <?php } ?>
   <?php if($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1){ ?>
      <p class="no-mbot">
         <span class="bold"><?php echo _l('project'); ?>:</span>
         <?php echo get_project_name_by_id($invoice->project_id); ?>
      </p>
   <?php } ?>
   <?php $pdf_custom_fields = get_custom_fields('invoice',array('show_on_pdf'=>1));
   foreach($pdf_custom_fields as $field){
    $value = get_custom_field_value($invoice->id,$field['id'],'invoice');
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
         <?php
         $items = get_items_table_data($invoice, 'invoice', 'html', true);
         echo $items->table();
         ?>
      </div>
   </div><?php
    $CI = & get_instance();
    $CI->load->model('invoices_model');
    $sum_custom = $CI->invoices_model->sumcustom($invoice->id);
    foreach ($sum_custom as $custom){
      $CI->db->select("fieldid,sum(value) as total");
            $CI->db->from('tblitemable');
            $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
            $CI->db->where('tblitemable.rel_id',$invoice->id);
            $CI->db->where('tblcustomfieldsvalues.fieldid',$custom->id);
            $CI->db->where('tblitemable.rel_type',"invoice");
            $CI->db->where('tblcustomfieldsvalues.value!=1');
            $CI->db->order_by("fieldid","asc");
            $query = $CI->db->get()->result();
            if($query[0]->total==""){
                $query[0]->total=0;
            }
            
      
          echo'<p><strong>Total ' .  $custom->name . '=</strong>' .  $query[0]->total . '</p>';
    }?>
   <div class="col-md-5 col-md-offset-7">
      <table class="table text-right">
         <tbody>
            <tr id="subtotal">
               <td><span class="bold"><?php echo _l('invoice_subtotal'); ?></span>
               </td>
               <td class="subtotal">
                  <?php echo app_format_money($invoice->subtotal, $invoice->currency_name); ?>
               </td>
            </tr>
            <?php if(is_sale_discount_applied($invoice)){ ?>
               <tr>
                  <td>
                     <span class="bold"><?php echo _l('invoice_discount'); ?>
                     <?php if(is_sale_discount($invoice,'percent')){ ?>
                        (<?php echo app_format_number($invoice->discount_percent,true); ?>%)
                        <?php } ?></span>
                     </td>
                     <td class="discount">
                        <?php echo '-' . app_format_money($invoice->discount_total, $invoice->currency_name); ?>
                     </td>
                  </tr>
               <?php } ?>
               <?php
               $taxto=0;
                foreach ($items->taxes() as $tax) {
                    $taxto= $taxto+ $tax['total_tax'];
                    echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('.app_format_number($tax['taxrate']).'%)</td><td>'.app_format_money($tax['total_tax'], $invoice->currency_name).'</td></tr>';
              }
              ?>
              <?php if((int)$invoice->adjustment >= 0){ ?>
               <tr>
                  <td>
                     <span class="bold"><?php echo _l('invoice_adjustment'); ?></span>
                  </td>
                  <td class="adjustment">
                     <?php echo app_format_money($invoice->total-($taxto-$invoice->discount_total+$invoice->subtotal), $invoice->currency_name); ?>
                  </td>
               </tr>
            <?php } ?>
            <tr>
               <td><span class="bold"><?php echo _l('invoice_total'); ?></span>
               </td>
               <td class="total">
                  <?php echo app_format_money($invoice->total, $invoice->currency_name); ?>
               </td>
            </tr>
            <tr>
               <td><span class="bold"><?php echo _l('Shipping Expense'); ?></span>
               </td>
               <td class="total">
                  <?php echo app_format_money($invoice->ship_expense, $invoice->currency_name); ?>
               </td>
            </tr><tr>
               <td><span class="bold"><?php echo _l('Other Expense'); ?></span>
               </td>
               <td class="total">
                  <?php echo app_format_money($invoice->other_expense, $invoice->currency_name); ?>
               </td>
            </tr>
            <?php if(count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) { ?>
               <tr>
                  <td><span class="bold"><?php echo _l('invoice_total_paid'); ?></span></td>
                  <td>
                     <?php echo '-' . app_format_money(sum_from_table(db_prefix().'invoicepaymentrecords',array('field'=>'amount','where'=>array('invoiceid'=>$invoice->id))), $invoice->currency_name); ?>
                  </td>
               </tr>
            <?php } ?>
            <?php if(get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)){ ?>
               <tr>
                  <td><span class="bold"><?php echo _l('applied_credits'); ?></span></td>
                  <td>
                     <?php echo '-' . app_format_money($credits_applied, $invoice->currency_name); ?>
                  </td>
               </tr>
            <?php } ?>
            <?php if(get_option('show_amount_due_on_invoice') == 1 && $invoice->status != Invoices_model::STATUS_CANCELLED) { ?>
               <tr>
                  <td><span class="<?php if($invoice->total_left_to_pay > 0){echo 'text-danger ';} ?>bold"><?php echo _l('invoice_amount_due'); ?></span></td>
                  <td>
                     <span class="<?php if($invoice->total_left_to_pay > 0){echo 'text-danger';} ?>">
                        <?php echo app_format_money($invoice->total_left_to_pay, $invoice->currency_name); ?>
                     </span>
                  </td>
               </tr>
               <?php
                              $CI =&get_instance();
                                      // Invoiced amount during the period
                                        $result['invoiced_amount'] = $CI->db->query('SELECT
                                        SUM(' . db_prefix() . 'invoices.total) as invoiced_amount
                                        FROM ' . db_prefix() . 'invoices
                                        WHERE clientid = ' . $invoice->clientid . '
                                        AND status != ' . Invoices_model::STATUS_DRAFT . ' AND status != ' . Invoices_model::STATUS_CANCELLED . '')
                                            ->row()->invoiced_amount;
                                
                                        if ($result['invoiced_amount'] === null) {
                                            $result['invoiced_amount'] = 0;
                                        }
                                
                                        $result['credit_notes_amount'] = $CI->db->query('SELECT
                                        SUM(' . db_prefix() . 'creditnotes.total) as credit_notes_amount
                                        FROM ' . db_prefix() . 'creditnotes
                                        WHERE clientid = ' . $invoice->clientid)->row()->credit_notes_amount;
                                
                                        if ($result['credit_notes_amount'] === null) {
                                            $result['credit_notes_amount'] = 0;
                                        }
                                        $result['refunds_amount'] = $CI->db->query('SELECT
                                        SUM(' . db_prefix() . 'creditnote_refunds.amount) as refunds_amount
                                        FROM ' . db_prefix() . 'creditnote_refunds
                                        WHERE credit_note_id IN (SELECT id FROM ' . db_prefix() . 'creditnotes where clientid=' . $invoice->clientid . ')')->row()->refunds_amount;
                                
                                        if ($result['refunds_amount'] === null) {
                                            $result['refunds_amount'] = 0;
                                        }
                                
                                        $result['invoiced_amount'] = $result['invoiced_amount'];
                                
                                        // Amount paid during the period
                                        $result['amount_paid'] = $CI->db->query('SELECT
                                        SUM(' . db_prefix() . 'invoicepaymentrecords.amount) as amount_paid
                                        FROM ' . db_prefix() . 'invoicepaymentrecords
                                        JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid
                                        WHERE ' . db_prefix() . 'invoices.clientid = ' . $invoice->clientid)
                                            ->row()->amount_paid;
                                
                                        if ($result['amount_paid'] === null) {
                                            $result['amount_paid'] = 0;
                                        }
                                        
                                        
                                        $result['direct_paid'] = $CI->db->query('SELECT
                                        SUM(' . db_prefix() . 'invoicepaymentrecords.amount) as amount_paid
                                        FROM ' . db_prefix() . 'invoicepaymentrecords
                                        WHERE client_id = ' . $invoice->clientid)
                                            ->row()->amount_paid;
                                        if ($result['direct_paid'] === null) {
                                            $result['direct_paid'] = 0;
                                        }
                                
                                
                                            $result['beginning_balance'] = 0;
                                            $abc =  ($CI->db->select("balance")->from('tblclients')->where('userid', $invoice->clientid)->get()->result());
                                        $result['beginning_balance'] += (float)$abc[0]->balance;
                                        
                                        $sql_expense = 'SELECT
                                        SUM(' . db_prefix() . 'expenses.amount) as invoice_amount
                                        FROM ' . db_prefix() . 'expenses
                                        WHERE '. db_prefix() . 'expenses.clientid = ' . $invoice->clientid . '
                                        ORDER by ' . db_prefix() . 'expenses.date DESC';
                                
                                        $sumexpense = $CI->db->query($sql_expense)->row()->invoice_amount;
                                        if ($sumexpense==NULL){
                                            $sumexpense=0;
                                        }
                                        $dec = get_decimal_places();
                                        
                                            $result['balance_due'] = number_format($result['invoiced_amount'] - $result['amount_paid'], $dec, '.', '');
                                            $result['balance_due'] = $result['balance_due'] + number_format($result['beginning_balance'], $dec, '.', '');
                                            $result['balance_due'] = $result['balance_due'] - number_format($result['direct_paid'], $dec, '.', '');
                                            // var_dump($CI->db->last_query());die;
                                            $result['balance_due'] = $result['balance_due'] - number_format($result['refunds_amount'], $dec, '.', '');
                                        $result['balance_due']=$result['balance_due'] + $sumexpense;
                              
                              ?>
                              <tr>
                                 <td>Balance: <input type="password" value="<?php echo $result['balance_due']?>" id="myInput" disabled></td>
                                <td><button onclick="myFunction()">Show Balance</button></td>
                                
                                <script>
                                function myFunction() {
                                  var x = document.getElementById("myInput");
                                  if (x.type === "password") {
                                    x.type = "text";
                                  } else {
                                    x.type = "password";
                                  }
                                }
                                </script>
                                 
                              </tr>
            <?php } ?>
         </tbody>
      </table>
   </div>
</div>
<?php if(count($invoice->attachments) > 0){ ?>
   <div class="clearfix"></div>
   <hr />
   <p class="bold text-muted"><?php echo _l('invoice_files'); ?></p>
   <?php foreach($invoice->attachments as $attachment){
      $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
      if(!empty($attachment['external'])){
         $attachment_url = $attachment['external_link'];
      }
      ?>
      <div class="mbot15 row inline-block full-width" data-attachment-id="<?php echo $attachment['id']; ?>">
         <div class="col-md-8">
            <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
            <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
            <br />
            <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
         </div>
         <div class="col-md-4 text-right">
            <?php if($attachment['visible_to_customer'] == 0){
               $icon = 'fa-toggle-off';
               $tooltip = _l('show_to_customer');
            } else {
               $icon = 'fa-toggle-on';
               $tooltip = _l('hide_from_customer');
            }
            ?>
            <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $invoice->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="fa <?php echo $icon; ?>" aria-hidden="true"></i></a>
            <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
               <a href="#" class="text-danger" onclick="delete_invoice_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
            <?php } ?>
         </div>
      </div>
   <?php } ?>
<?php } ?>
<hr />
<?php if($invoice->clientnote != ''){ ?>
   <div class="col-md-12 row mtop15">
      <p class="bold text-muted"><?php echo _l('invoice_note'); ?></p>
      <p><?php echo $invoice->clientnote; ?></p>
   </div>
<?php } ?>
<?php if($invoice->terms != ''){ ?>
   <div class="col-md-12 row mtop15">
      <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
      <p><?php echo $invoice->terms; ?></p>
   </div>
<?php } ?>
</div>
