<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="col-md-12 no-padding">
   <div class="panel_s">
      <div class="panel-body">
         <div class="horizontal-scrollable-tabs preview-tabs-top">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
               <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                  <li role="presentation" class="active">
                     <a href="#tab_proforma" aria-controls="tab_proforma" role="tab" data-toggle="tab">
                     <?php echo _l('proforma_invoice'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_tasks" onclick="init_rel_tasks_table(<?php echo $proforma->id; ?>,'proforma'); return false;" aria-controls="tab_tasks" role="tab" data-toggle="tab">
                     <?php echo _l('tasks'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_notes" onclick="get_sales_notes(<?php echo $proforma->id; ?>,'proforma'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
                     <?php echo _l('estimate_notes'); ?> <span class="notes-total">
                     <?php if($totalNotes > 0){ ?>
                     <span class="badge"><?php echo $totalNotes; ?></span>
                     <?php } ?>
                     </span>
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
               <?php echo format_invoice_status($proforma->status,'mtop5'); ?>
            </div>
            <div class="col-md-9 _buttons">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <div class="pull-right">
                  <?php if(has_permission('proforma','','edit')){ ?>
                  <a href="<?php echo admin_url('proforma/invoice/'.$proforma->id); ?>" data-toggle="tooltip" title="<?php echo _l('edit_proforma_invoice_tooltip'); ?>" class="btn btn-default btn-with-tooltip" data-placement="bottom"><i class="fa fa-pencil-square-o"></i></a>
                  <?php } ?>
                  <?php if(has_permission('proforma','','edit')){ ?>
                     <a href="#" data-toggle="modal" data-target="#payment_record" class="btn btn-default btn-with-tooltip" data-placement="bottom" title="<?php echo _l('invoice_record_payment'); ?>"><i class="fa fa-credit-card"></i></a>
                  <?php } ?>
                  <div class="btn-group">
                     <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php if(is_mobile()){echo ' PDF';} ?> <span class="caret"></span></a>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li class="hidden-xs"><a href="<?php echo admin_url('proforma/pdf/'.$proforma->id.'?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                        <li class="hidden-xs"><a href="<?php echo admin_url('proforma/pdf/'.$proforma->id.'?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                        <li><a href="<?php echo admin_url('proforma/pdf/'.$proforma->id); ?>"><?php echo _l('download'); ?></a></li>
                        <li>
                           <a href="<?php echo admin_url('proforma/pdf/'.$proforma->id.'?print=true'); ?>" target="_blank">
                           <?php echo _l('print'); ?>
                           </a>
                        </li>
                     </ul>
                  </div>
                  <a href="#" onclick="proforma_email_send(<?php echo $proforma->id; ?>); return false;" class="btn-with-tooltip btn btn-default" data-toggle="tooltip" title="<?php echo _l('invoice_send_to_email_tooltip'); ?>" data-placement="bottom"><i class="fa fa-envelope"></i></a>
                  
                  <?php if(has_permission('proforma','','create') || has_permission('invoices','','create') || has_permission('estimates','','create') || has_permission('proposals','','create')){ ?>
                  <div class="btn-group pull-right mleft5">
                     <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <?php echo _l('convert'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu">
                        <?php if(has_permission('proforma','','create') || has_permission('invoices','','create')){ ?>
                        <li><a href="<?php echo admin_url('proforma/convert/'.$proforma->id); ?>"><?php echo _l('convert_to_invoice'); ?></a></li>
                        <?php } ?>
                        <?php if(has_permission('estimates','','create')){ ?>
                        <li><a href="<?php echo admin_url('proforma/convert_to_estimate/'.$proforma->id); ?>">Convert to Estimate</a></li>
                        <?php } ?>
                        <?php if(has_permission('proposals','','create')){ ?>
                        <li><a href="<?php echo admin_url('proforma/convert_to_proposal/'.$proforma->id); ?>">Convert to Proposal</a></li>
                        <?php } ?>
                     </ul>
                  </div>
                  <?php } ?>
                  <div class="btn-group">
                     <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <?php echo _l('more'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="<?php echo admin_url('proforma/view/' . $proforma->id . '/' .  $proforma->hash) ?>" target="_blank"><?php echo _l('view_invoice_as_customer_tooltip'); ?></a></li>
                        <li>
                           <a href="<?php echo admin_url('proforma/delete/'.$proforma->id); ?>" class="text-danger delete-text _delete"><?php echo _l('delete_invoice'); ?></a>
                        </li>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="tab_proforma">
               <div id="proforma-preview">
                  <?php $this->load->view('proforma_partial_html'); ?>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_tasks">
               <?php init_relation_tasks_table(array('data-new-rel-id'=>$proforma->id,'data-new-rel-type'=>'proforma')); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_notes">
               <?php echo form_open(admin_url('proforma/add_note/'.$proforma->id),array('id'=>'sales-notes','class'=>'proforma-notes-form')); ?>
               <?php echo render_textarea('description'); ?>
               <div class="text-right">
                  <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('estimate_add_note'); ?></button>
               </div>
               <?php echo form_close(); ?>
               <hr />
               <div class="panel_s mtop20 no-shadow" id="sales_notes_area"></div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="modal fade" id="payment_record" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('invoice_record_payment'); ?></h4>
            </div>
            <?php echo form_open('admin/proforma/record_payment', array('id'=>'payment-record-form')); ?>
            <?php echo form_hidden('invoiceid', $proforma->id); ?>
            <div class="modal-body">
                <?php echo render_input('amount','record_payment_amount_received',($proforma->total - sum_from_table(db_prefix().'proforma_invoice_payment_records',array('field'=>'amount','where'=>array('proforma_invoice_id'=>$proforma->id)))),'number',array('max'=>$proforma->total)); ?>
                <?php echo render_date_input('date','record_payment_date',_d(date('Y-m-d'))); ?>
                <div class="form-group">
                    <label for="paymentmode" class="control-label"><?php echo _l('payment_mode'); ?></label>
                    <select class="selectpicker" name="paymentmode" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                    <option value=""></option>
                    <?php foreach($payment_modes as $mode){ ?>
                    <option value="<?php echo $mode['id']; ?>"><?php echo $mode['name']; ?></option>
                    <?php } ?>
                    </select>
                </div>
                <?php echo render_input('transactionid','payment_transaction_id'); ?>
                <?php echo render_textarea('note','payment_note'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
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
</script>
