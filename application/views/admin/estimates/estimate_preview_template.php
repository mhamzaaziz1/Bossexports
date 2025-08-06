<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('_attachment_sale_id',$estimate->id); ?>
<?php echo form_hidden('_attachment_sale_type','estimate'); ?>
<div class="col-md-12 no-padding">
   <div class="panel_s">
      <div class="panel-body">
         <div class="horizontal-scrollable-tabs preview-tabs-top">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
               <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                  <li role="presentation" class="active">
                     <a href="#tab_estimate" aria-controls="tab_estimate" role="tab" data-toggle="tab">
                     <?php echo _l('estimate'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_tasks" onclick="init_rel_tasks_table(<?php echo $estimate->id; ?>,'estimate'); return false;" aria-controls="tab_tasks" role="tab" data-toggle="tab">
                     <?php echo _l('tasks'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab">
                     <?php echo _l('estimate_view_activity_tooltip'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $estimate->id ;?> + '/' + 'estimate', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                     <?php echo _l('estimate_reminders'); ?>
                     <?php
                        $total_reminders = total_rows(db_prefix().'reminders',
                          array(
                           'isnotified'=>0,
                           'staff'=>get_staff_user_id(),
                           'rel_type'=>'estimate',
                           'rel_id'=>$estimate->id
                           )
                          );
                        if($total_reminders > 0){
                          echo '<span class="badge">'.$total_reminders.'</span>';
                        }
                        ?>
                     </a>
                  </li>
                  <li role="presentation" class="tab-separator">
                     <a href="#tab_notes" onclick="get_sales_notes(<?php echo $estimate->id; ?>,'estimates'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
                     <?php echo _l('estimate_notes'); ?>
                     <span class="notes-total">
                        <?php if($totalNotes > 0){ ?>
                           <span class="badge"><?php echo $totalNotes; ?></span>
                        <?php } ?>
                     </span>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" title="<?php echo _l('emails_tracking'); ?>" class="tab-separator">
                     <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking" role="tab" data-toggle="tab">
                     <?php if(!is_mobile()){ ?>
                     <i class="fa fa-envelope-open-o" aria-hidden="true"></i>
                     <?php } else { ?>
                     <?php echo _l('emails_tracking'); ?>
                     <?php } ?>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('view_tracking'); ?>" class="tab-separator">
                     <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
                     <?php if(!is_mobile()){ ?>
                     <i class="fa fa-eye"></i>
                     <?php } else { ?>
                     <?php echo _l('view_tracking'); ?>
                     <?php } ?>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="tab-separator toggle_view">
                     <a href="#" onclick="small_table_full_view(); return false;">
                     <i class="fa fa-expand"></i></a>
                  </li>
               </ul>
            </div>
         </div>
         <div class="row mtop10">
            <div class="col-md-3">
               <?php echo format_estimate_status($estimate->status,'mtop5');  ?>
                    <?php if($estimate->retained == 1){?>
                    <span class="label label-default mtop5 s-status estimate-status-1 estimate-status-default">Retained Released</span>
                  <?php }else{?>
                  <span class="label label-default mtop5 s-status estimate-status-1 estimate-status-default">Retained Onhold</span>
                  <?php } ?>
            </div>
            <div class="col-md-9">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <div class="pull-right _buttons">
                  <?php if(has_permission('estimates','','edit')){ ?>
                  <a href="<?php echo admin_url('estimates/estimate/'.$estimate->id); ?>" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('edit_estimate_tooltip'); ?>" data-placement="bottom"><i class="fa fa-pencil-square-o"></i></a>
                  <?php } ?>
                  <div class="btn-group">
                     <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php if(is_mobile()){echo ' PDF';} ?> <span class="caret"></span></a>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li class="hidden-xs"><a href="<?php echo admin_url('estimates/pdf/'.$estimate->id.'?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                        <li class="hidden-xs"><a href="<?php echo admin_url('estimates/pdf/'.$estimate->id.'?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                        <li><a href="<?php echo admin_url('estimates/pdf/'.$estimate->id); ?>"><?php echo _l('download'); ?></a></li>
                        <li>
                           <a href="<?php echo admin_url('estimates/pdf/'.$estimate->id.'?print=true'); ?>" target="_blank">
                           <?php echo _l('print'); ?>
                           </a>
                        </li>
                        <li class="hidden-xs"><a href="<?php echo admin_url('estimates/pdf/'.$estimate->id.'?output_type=I&type=performa'); ?>">Packing Slip</a></li>
                        <li class="hidden-xs"><a href="<?php echo admin_url('estimates/pdf/'.$estimate->id.'?output_type=I&type=pslip'); ?>">Packing Sticker</a></li>
                     </ul>
                  </div>
                  <?php
                     $_tooltip = _l('estimate_sent_to_email_tooltip');
                     $_tooltip_already_send = '';
                     if($estimate->sent == 1){
                        $_tooltip_already_send = _l('estimate_already_send_to_client_tooltip', time_ago($estimate->datesend));
                     }
                     ?>
                  <?php if(!empty($estimate->clientid)){ ?>
                  <a href="#" class="estimate-send-to-client btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo $_tooltip; ?>" data-placement="bottom"><span data-toggle="tooltip" data-title="<?php echo $_tooltip_already_send; ?>"><i class="fa fa-envelope"></i></span></a>
                  <?php } ?>
                  <div class="btn-group">
                     <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <?php echo _l('more'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li>
                           <a href="<?php echo site_url('estimate/' . $estimate->id . '/' .  $estimate->hash) ?>" target="_blank">
                           <?php echo _l('view_estimate_as_client'); ?>
                           </a>
                        </li>
                        <?php hooks()->do_action('after_estimate_view_as_client_link', $estimate); ?>
                        <?php if((!empty($estimate->expirydate) && date('Y-m-d') < $estimate->expirydate && ($estimate->status == 2 || $estimate->status == 5)) && is_estimates_expiry_reminders_enabled()){ ?>
                        <li>
                           <a href="<?php echo admin_url('estimates/send_expiry_reminder/'.$estimate->id); ?>">
                           <?php echo _l('send_expiry_reminder'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <li>
                           <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                        </li>
                        <?php if($estimate->invoiceid == NULL){
                           if(has_permission('estimates','','edit')){
                             foreach($estimate_statuses as $status){
                               if($estimate->status != $status){ ?>
                        <li>
                           <a href="<?php echo admin_url() . 'estimates/mark_action_status/'.$status.'/'.$estimate->id; ?>">
                           <?php echo _l('estimate_mark_as',format_estimate_status($status,'',false)); ?></a>
                        </li>
                        <?php }
                           }
                           ?>
                        <?php } ?>
                        <?php } ?>
                        <?php if(has_permission('estimates','','create')){ ?>
                        <li>
                           <a href="<?php echo admin_url('estimates/copy/'.$estimate->id); ?>">
                           <?php echo _l('copy_estimate'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <?php if(!empty($estimate->signature) && has_permission('estimates','','delete')){ ?>
                        <li>
                           <a href="<?php echo admin_url('estimates/clear_signature/'.$estimate->id); ?>" class="_delete">
                           <?php echo _l('clear_signature'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <?php if(has_permission('estimates','','delete')){ ?>
                        <?php
                           if((get_option('delete_only_on_last_estimate') == 1 && is_last_estimate($estimate->id)) || (get_option('delete_only_on_last_estimate') == 0)){ ?>
                        <li>
                           <a href="<?php echo admin_url('estimates/delete/'.$estimate->id); ?>" class="text-danger delete-text _delete"><?php echo _l('delete_estimate_tooltip'); ?></a>
                        </li>
                        <?php
                           }
                           }
                           ?>
                     </ul>
                  </div>
                  <?php if($estimate->retained == 0){?>
                  <a href="<?php echo admin_url('estimates/release_retainer/'.$estimate->id); ?>"><button type="button" class="btn btn-success" >
                     <?php echo _l('Release Retained Amount'); ?>
                  </button></a>
                  <?php } ?>
                  <br><br>
                  <?php if($estimate->invoiceid == NULL){ ?>
                  <?php if(has_permission('invoices','','create') && !empty($estimate->clientid)){ ?>
                  <div class="btn-group pull-right mleft5">
                     <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <?php echo _l('estimate_convert_to_invoice'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu">
                        <li><a href="<?php echo admin_url('estimates/convert_to_invoice/'.$estimate->id.'?save_as_draft=true'); ?>"><?php echo _l('convert_and_save_as_draft'); ?></a></li>
                        <li class="divider">
                        <li><a href="<?php echo admin_url('estimates/convert_to_invoice/'.$estimate->id); ?>"><?php echo _l('convert'); ?></a></li>
                        </li>
                     </ul>
                  </div>
                  <?php } ?>
                  <?php } else { ?>
                  <a href="<?php echo admin_url('invoices/list_invoices/'.$estimate->invoice->id); ?>" data-placement="bottom" data-toggle="tooltip" title="<?php echo _l('estimate_invoiced_date',_dt($estimate->invoiced_date)); ?>"class="btn mleft10 btn-info"><?php echo format_invoice_number($estimate->invoice->id); ?></a>
                  <?php } ?>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane ptop10 active" id="tab_estimate">
               <?php if(isset($estimate->scheduled_email) && $estimate->scheduled_email) { ?>
                     <div class="alert alert-warning">
                        <?php echo _l('invoice_will_be_sent_at', _dt($estimate->scheduled_email->scheduled_at)); ?>
                        <?php if(staff_can('edit', 'estimates') || $estimate->addedfrom == get_staff_user_id()) { ?>
                           <a href="#"
                           onclick="edit_estimate_scheduled_email(<?php echo $estimate->scheduled_email->id; ?>); return false;">
                           <?php echo _l('edit'); ?>
                        </a>
                     <?php } ?>
                  </div>
               <?php } ?>
               <div id="estimate-preview">
                  <div class="row">
                     <?php if($estimate->status == 4 && !empty($estimate->acceptance_firstname) && !empty($estimate->acceptance_lastname) && !empty($estimate->acceptance_email)){ ?>
                     <div class="col-md-12">
                        <div class="alert alert-info mbot15">
                           <?php echo _l('accepted_identity_info',array(
                              _l('estimate_lowercase'),
                              '<b>'.$estimate->acceptance_firstname . ' ' . $estimate->acceptance_lastname . '</b> (<a href="mailto:'.$estimate->acceptance_email.'">'.$estimate->acceptance_email.'</a>)',
                              '<b>'. _dt($estimate->acceptance_date).'</b>',
                              '<b>'.$estimate->acceptance_ip.'</b>'.(is_admin() ? '&nbsp;<a href="'.admin_url('estimates/clear_acceptance_info/'.$estimate->id).'" class="_delete text-muted" data-toggle="tooltip" data-title="'._l('clear_this_information').'"><i class="fa fa-remove"></i></a>' : '')
                              )); ?>
                        </div>
                     </div>
                     <?php } ?>
                     <?php if($estimate->project_id != 0){ ?>
                     <div class="col-md-12">
                        <h4 class="font-medium mbot15"><?php echo _l('related_to_project',array(
                           _l('estimate_lowercase'),
                           _l('project_lowercase'),
                           '<a href="'.admin_url('projects/view/'.$estimate->project_id).'" target="_blank">' . $estimate->project_data->name . '</a>',
                           )); ?></h4>
                     </div>
                     <?php } ?>
                     <div class="col-md-6 col-sm-6">
                        <h4 class="bold">
                           <?php
                              $tags = get_tags_in($estimate->id,'estimate');
                              if(count($tags) > 0){
                                echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="'.html_escape(implode(', ',$tags)).'"></i>';
                              }
                              ?>
                           <a href="<?php echo admin_url('estimates/estimate/'.$estimate->id); ?>">
                           <span id="estimate-number">
                           <?php echo format_estimate_number($estimate->id); ?>
                           </span>
                           </a>
                        </h4>
                        <address>
                           <?php echo format_organization_info(); ?>
                        </address>
                     </div>
                     <div class="col-sm-6 text-right">
                        <span class="bold"><?php echo _l('estimate_to'); ?>:</span>
                        <address>
                           <?php echo format_customer_info($estimate, 'estimate', 'billing', true); ?>
                        </address>
                        <?php if($estimate->include_shipping == 1 && $estimate->show_shipping_on_estimate == 1){ ?>
                        <span class="bold"><?php echo _l('ship_to'); ?>:</span>
                        <address>
                           <?php echo format_customer_info($estimate, 'estimate', 'shipping'); ?>
                        </address>
                        <?php } ?>
                        <p class="no-mbot">
                           <span class="bold">
                           <?php echo _l('estimate_data_date'); ?>:
                           </span>
                           <?php echo $estimate->date; ?>
                        </p>
                        <?php if(!empty($estimate->expirydate)){ ?>
                        <p class="no-mbot">
                           <span class="bold"><?php echo _l('estimate_data_expiry_date'); ?>:</span>
                           <?php echo $estimate->expirydate; ?>
                        </p>
                        <?php } ?>
                        <?php if(!empty($estimate->reference_no)){ ?>
                        <p class="no-mbot">
                           <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                           <?php echo $estimate->reference_no; ?>
                        </p>
                        <?php } ?>
                        <?php if($estimate->sale_agent != 0 && get_option('show_sale_agent_on_estimates') == 1){ ?>
                        <p class="no-mbot">
                           <span class="bold"><?php echo _l('sale_agent_string'); ?>:</span>
                           <?php echo get_staff_full_name($estimate->sale_agent); ?>
                        </p>
                        <?php } ?>
                        <?php if($estimate->project_id != 0 && get_option('show_project_on_estimate') == 1){ ?>
                        <p class="no-mbot">
                           <span class="bold"><?php echo _l('project'); ?>:</span>
                           <?php echo get_project_name_by_id($estimate->project_id); ?>
                        </p>
                        <?php } ?>
                        <?php $pdf_custom_fields = get_custom_fields('estimate',array('show_on_pdf'=>1));
                           foreach($pdf_custom_fields as $field){
                           $value = get_custom_field_value($estimate->id,$field['id'],'estimate');
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
                                 $items = get_items_table_data($estimate, 'estimate', 'html', true);
                                 echo $items->table();
                              ?>
                        </div>
                     </div>
                     <?php
                    $CI = & get_instance();
                    $CI->load->model('invoices_model');
                    $sum_custom = $CI->invoices_model->sumcustom($estimate->id);
                    foreach ($sum_custom as $custom){
                      $CI->db->select("fieldid,sum(value) as total");
                            $CI->db->from('tblitemable');
                            $CI->db->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid = tblitemable.id', 'right outer');
                            $CI->db->where('tblitemable.rel_id',$estimate->id);
                            $CI->db->where('tblcustomfieldsvalues.fieldid',$custom->id);
                            $CI->db->where('tblitemable.rel_type',"estimate");
                            $CI->db->where('tblcustomfieldsvalues.value!=1');
                            $CI->db->order_by("fieldid","asc");
                            $query = $CI->db->get()->result();
                            
                            if($query[0]->total==""){
                                $query[0]->total=0;
                            }
                            
                      echo '<p><strong>Total ' .  $custom->name . '=</strong>' .  $query[0]->total . '</p>';
                    }?>
                     <div class="col-md-5 col-md-offset-7">
                        <table class="table text-right">
                           <tbody>
                              <tr id="subtotal">
                                 <td><span class="bold"><?php echo _l('estimate_subtotal'); ?></span>
                                 </td>
                                 <td class="subtotal">
                                    <?php echo app_format_money($estimate->subtotal, $estimate->currency_name); ?>
                                 </td>
                              </tr>
                              <?php if(is_sale_discount_applied($estimate)){ ?>
                              <tr>
                                 <td>
                                    <span class="bold"><?php echo _l('estimate_discount'); ?>
                                    <?php if(is_sale_discount($estimate,'percent')){ ?>
                                    (<?php echo app_format_number($estimate->discount_percent,true); ?>%)
                                    <?php } ?></span>
                                 </td>
                                 <td class="discount">
                                    <?php echo '-' . app_format_money($estimate->discount_total, $estimate->currency_name); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <?php
                              $taxto=0;
                                foreach ($items->taxes() as $tax) {
                                    $taxto= $taxto+ $tax['total_tax'];
                                     echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('.app_format_number($tax['taxrate']).'%)</td><td>'.app_format_money($tax['total_tax'], $estimate->currency_name).'</td></tr>';
                                 }
                                 ?>
                              <?php if((int)$estimate->adjustment >=0){ ?>
                              <tr>
                                 <td>
                                    <span class="bold"><?php echo _l('estimate_adjustment'); ?></span>
                                 </td>
                                 <td class="adjustment">
                                    <?php echo app_format_money($estimate->total-($taxto-$estimate->discount_total+$estimate->subtotal), $estimate->currency_name); ?>
                                 </td>
                              </tr>
                              <?php } ?>
                              <tr>
                                 <td><span class="bold"><?php echo _l('estimate_total'); ?></span>
                                 </td>
                                 <td class="total">
                                    <?php echo app_format_money($estimate->total, $estimate->currency_name); ?>
                                 </td>
                              </tr>
                              <tr>
                                   <td><span class="bold"><?php echo _l('Shipping Expense'); ?></span>
                                   </td>
                                   <td class="total">
                                      <?php echo app_format_money($estimate->ship_expense, $proposal->currency_name); ?>
                                   </td>
                                </tr>
                                <tr>
                                   <td><span class="bold"><?php echo _l('Other Expense'); ?></span>
                                   </td>
                                   <td class="total">
                                      <?php echo app_format_money($estimate->other_expense, $proposal->currency_name); ?>
                                   </td>
                                </tr>
                                <tr>
                                   <td><span class="bold"><?php echo _l('Total All'); ?></span>
                                   </td>
                                   <td class="total">
                                      <?php echo app_format_money(($estimate->total+$estimate->ship_expense+$estimate->other_expense), $proposal->currency_name); ?>
                                   </td>
                                </tr>
                              <?php
                              $CI =&get_instance();
                                      // Invoiced amount during the period
        $result['invoiced_amount'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'invoices.total) as invoiced_amount
        FROM ' . db_prefix() . 'invoices
        WHERE clientid = ' . $estimate->clientid . '
        AND status != ' . Invoices_model::STATUS_DRAFT . ' AND status != ' . Invoices_model::STATUS_CANCELLED . '')
            ->row()->invoiced_amount;

        if ($result['invoiced_amount'] === null) {
            $result['invoiced_amount'] = 0;
        }

        $result['credit_notes_amount'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'creditnotes.total) as credit_notes_amount
        FROM ' . db_prefix() . 'creditnotes
        WHERE clientid = ' . $estimate->clientid . '
        AND status != 3')
            ->row()->credit_notes_amount;

        if ($result['credit_notes_amount'] === null) {
            $result['credit_notes_amount'] = 0;
        }
        
        

        $result['refunds_amount'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'creditnote_refunds.amount) as refunds_amount
        FROM ' . db_prefix() . 'creditnote_refunds
        WHERE credit_note_id IN (SELECT id FROM ' . db_prefix() . 'creditnotes WHERE clientid=' . $estimate->clientid . ')')->row()->refunds_amount;

        if ($result['refunds_amount'] === null) {
            $result['refunds_amount'] = 0;
        }

        $result['invoiced_amount'] = $result['invoiced_amount'];

        // Amount paid during the period
        $result['amount_paid'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'invoicepaymentrecords.amount) as amount_paid
        FROM ' . db_prefix() . 'invoicepaymentrecords
        JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid
        WHERE ' . db_prefix() . 'invoices.clientid = ' . $estimate->clientid)
            ->row()->amount_paid;

        if ($result['amount_paid'] === null) {
            $result['amount_paid'] = 0;
        }
        
        
        $result['direct_paid'] = $CI->db->query('SELECT
        SUM(' . db_prefix() . 'invoicepaymentrecords.amount) as amount_paid
        FROM ' . db_prefix() . 'invoicepaymentrecords
        WHERE client_id = ' . $estimate->clientid)
            ->row()->amount_paid;
        if ($result['direct_paid'] === null) {
            $result['direct_paid'] = 0;
        }


            $result['beginning_balance'] = 0;
        $abc =  ($CI->db->select("balance")->from('tblclients')->where('userid', $estimate->clientid)->get()->result());
        $result['beginning_balance'] += (float)$abc[0]->balance;
        
        $sql_expense = 'SELECT
        SUM(' . db_prefix() . 'expenses.amount) as invoice_amount
        FROM ' . db_prefix() . 'expenses
        WHERE '. db_prefix() . 'expenses.clientid = ' . $estimate->clientid . ' AND tblexpenses.billable !=1
        ORDER by ' . db_prefix() . 'expenses.date DESC';

        $result['sumexpense'] = $CI->db->query($sql_expense)->row()->invoice_amount;

        $dec = get_decimal_places();
        
            $result['balance_due_'] = number_format($result['invoiced_amount'] - $result['amount_paid'], $dec, '.', '');
            $result['balance_due_'] = $result['balance_due_'] + number_format($result['beginning_balance'], $dec, '.', '');
            $result['balance_due_'] = $result['balance_due_'] - number_format($result['direct_paid'], $dec, '.', '');
            // var_dump($result);die;
            $result['balance_due_'] = $result['balance_due_'] - number_format($result['refunds_amount'], $dec, '.', '');
        $result['balance_due_']=$result['balance_due_'] + $result['sumexpense'];
            // var_dump($result);die;
                              
                              ?>
                              <tr>
                                 <td>Balance: <input type="password" value="<?php echo round($result['balance_due_'],2)?>" id="myInput" disabled></td>
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
                           </tbody>
                        </table>
                     </div>
                     <?php if(count($estimate->attachments) > 0){ ?>
                     <div class="clearfix"></div>
                     <hr />
                     <div class="col-md-12">
                        <p class="bold text-muted"><?php echo _l('estimate_files'); ?></p>
                     </div>
                     <?php foreach($estimate->attachments as $attachment){
                        $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                        if(!empty($attachment['external'])){
                          $attachment_url = $attachment['external_link'];
                        }
                        ?>
                     <div class="mbot15 row col-md-12" data-attachment-id="<?php echo $attachment['id']; ?>">
                        <div class="col-md-8">
                           <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                           <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
                           <br />
                           <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                        </div>
                        <div class="col-md-4 text-right">
                           <?php if($attachment['visible_to_customer'] == 0){
                              $icon = 'fa fa-toggle-off';
                              $tooltip = _l('show_to_customer');
                              } else {
                              $icon = 'fa fa-toggle-on';
                              $tooltip = _l('hide_from_customer');
                              }
                              ?>
                           <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $estimate->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="<?php echo $icon; ?>" aria-hidden="true"></i></a>
                           <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                           <a href="#" class="text-danger" onclick="delete_estimate_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                           <?php } ?>
                        </div>
                     </div>
                     <?php } ?>
                     <?php } ?>
                     <?php if($estimate->clientnote != ''){ ?>
                     <div class="col-md-12 mtop15">
                        <p class="bold text-muted"><?php echo _l('estimate_note'); ?></p>
                        <p><?php echo $estimate->clientnote; ?></p>
                     </div>
                     <?php } ?>
                     <?php if($estimate->terms != ''){ ?>
                     <div class="col-md-12 mtop15">
                        <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
                        <p><?php echo $estimate->terms; ?></p>
                     </div>
                     <?php } ?>
                  </div>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_tasks">
               <?php init_relation_tasks_table(array('data-new-rel-id'=>$estimate->id,'data-new-rel-type'=>'estimate')); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_reminders">
               <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-estimate-<?php echo $estimate->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('estimate_set_reminder_title'); ?></a>
               <hr />
               <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified')), 'reminders'); ?>
               <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$estimate->id,'name'=>'estimate','members'=>$members,'reminder_title'=>_l('estimate_set_reminder_title'))); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
               <?php
                  $this->load->view('admin/includes/emails_tracking',array(
                     'tracked_emails'=>
                     get_tracked_emails($estimate->id, 'estimate'))
                  );
                  ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_notes">
               <?php echo form_open(admin_url('estimates/add_note/'.$estimate->id),array('id'=>'sales-notes','class'=>'estimate-notes-form')); ?>
               <?php echo render_textarea('description'); ?>
               <div class="text-right">
                  <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('estimate_add_note'); ?></button>
               </div>
               <?php echo form_close(); ?>
               <hr />
               <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_activity">
               <div class="row">
                  <div class="col-md-12">
                     <div class="activity-feed">
                        <?php foreach($activity as $activity){
                           $_custom_data = false;
                           ?>
                        <div class="feed-item" data-sale-activity-id="<?php echo $activity['id']; ?>">
                           <div class="date">
                              <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($activity['date']); ?>">
                              <?php echo time_ago($activity['date']); ?>
                              </span>
                           </div>
                           <div class="text">
                              <?php if(is_numeric($activity['staffid']) && $activity['staffid'] != 0){ ?>
                              <a href="<?php echo admin_url('profile/'.$activity["staffid"]); ?>">
                              <?php echo staff_profile_image($activity['staffid'],array('staff-profile-xs-image pull-left mright5'));
                                 ?>
                              </a>
                              <?php } ?>
                              <?php
                                 $additional_data = '';
                                 if(!empty($activity['additional_data'])){
                                  $additional_data = unserialize($activity['additional_data']);
                                  $i = 0;
                                  foreach($additional_data as $data){
                                    if(strpos($data,'<original_status>') !== false){
                                      $original_status = get_string_between($data, '<original_status>', '</original_status>');
                                      $additional_data[$i] = format_estimate_status($original_status,'',false);
                                    } else if(strpos($data,'<new_status>') !== false){
                                      $new_status = get_string_between($data, '<new_status>', '</new_status>');
                                      $additional_data[$i] = format_estimate_status($new_status,'',false);
                                    } else if(strpos($data,'<status>') !== false){
                                      $status = get_string_between($data, '<status>', '</status>');
                                      $additional_data[$i] = format_estimate_status($status,'',false);
                                    } else if(strpos($data,'<custom_data>') !== false){
                                      $_custom_data = get_string_between($data, '<custom_data>', '</custom_data>');
                                      unset($additional_data[$i]);
                                    }
                                    $i++;
                                  }
                                 }
                                 $_formatted_activity = _l($activity['description'],$additional_data);
                                 if($_custom_data !== false){
                                 $_formatted_activity .= ' - ' .$_custom_data;
                                 }
                                 if(!empty($activity['full_name'])){
                                 $_formatted_activity = $activity['full_name'] . ' - ' . $_formatted_activity;
                                 }
                                 echo $_formatted_activity;
                                 if(is_admin()){
                                 echo '<a href="#" class="pull-right text-danger" onclick="delete_sale_activity('.$activity['id'].'); return false;"><i class="fa fa-remove"></i></a>';
                                 }
                                 ?>
                           </div>
                        </div>
                        <?php } ?>
                     </div>
                  </div>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_views">
               <?php
                  $views_activity = get_views_tracking('estimate',$estimate->id);
                  if(count($views_activity) === 0) {
                     echo '<h4 class="no-mbot">'._l('not_viewed_yet',_l('estimate_lowercase')).'</h4>';
                  }
                  foreach($views_activity as $activity){ ?>
               <p class="text-success no-margin">
                  <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
               </p>
               <p class="text-muted">
                  <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
               </p>
               <hr />
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
   init_items_sortable(true);
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();
   <?php if($send_later) { ?>
      schedule_estimate_send(<?php echo $estimate->id; ?>);
   <?php } ?>
</script>
<?php $this->load->view('admin/estimates/estimate_send_to_client'); ?>
