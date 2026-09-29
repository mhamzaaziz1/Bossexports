<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('_attachment_sale_id',$quote->id); ?>
<?php echo form_hidden('_attachment_sale_type','quote'); ?>
<div class="panel_s">
   <div class="panel-body">
      <div class="horizontal-scrollable-tabs preview-tabs-top">
         <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
         <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
         <div class="horizontal-tabs">
            <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
               <li role="presentation" class="active">
                  <a href="#tab_quote" aria-controls="tab_quote" role="tab" data-toggle="tab">
                  <?php echo _l('quote'); ?>
                  </a>
               </li>
               <?php if(isset($quote)){ ?>
               <li role="presentation">
                  <a href="#tab_comments" onclick="get_quote_comments(); return false;" aria-controls="tab_comments" role="tab" data-toggle="tab">
                  <?php echo _l('quote_comments'); ?>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $quote->id ;?> + '/' + 'quote', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                  <?php echo _l('estimate_reminders'); ?>
                  <?php
                     $total_reminders = total_rows(db_prefix().'reminders',
                      array(
                       'isnotified'=>0,
                       'staff'=>get_staff_user_id(),
                       'rel_type'=>'quote',
                       'rel_id'=>$quote->id
                       )
                      );
                     if($total_reminders > 0){
                      echo '<span class="badge">'.$total_reminders.'</span>';
                     }
                     ?>
                  </a>
               </li>
               <li role="presentation" class="tab-separator">
                  <a href="#tab_tours" onclick="init_rel_tours_table(<?php echo $quote->id; ?>,'quote'); return false;" aria-controls="tab_tours" role="tab" data-toggle="tab">
                  <?php echo _l('tours'); ?>
                  </a>
               </li>
                 <li role="presentation" class="tab-separator">
                     <a href="#tab_notes" onclick="get_sales_notes(<?php echo $quote->id; ?>,'quotes'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
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
               <?php } ?>
            </ul>
         </div>
      </div>
      <div class="row mtop10">
         <div class="col-md-3">
            <?php echo format_quote_status($quote->status,'pull-left mright5 mtop5'); ?>
         </div>
         <div class="col-md-9 text-right _buttons quote_buttons">
            <?php if(has_permission('quotes','','edit')){ ?>
            <a href="<?php echo admin_url('booking/quotes/quote/'.$quote->id); ?>" data-placement="left" data-toggle="tooltip" title="<?php echo _l('quote_edit'); ?>" class="btn btn-default btn-with-tooltip" data-placement="bottom"><i class="fa-regular fa-pen-to-square"></i></a>
            <?php } ?>
            <div class="btn-group">
               <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa-regular fa-file-pdf"></i><?php if(is_mobile()){echo ' PDF';} ?> <span class="caret"></span></a>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li class="hidden-xs"><a href="<?php echo admin_url('booking/quotes/pdf/'.$quote->id.'?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                  <li class="hidden-xs"><a href="<?php echo admin_url('booking/quotes/pdf/'.$quote->id.'?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                  <li><a href="<?php echo admin_url('booking/quotes/pdf/'.$quote->id); ?>"><?php echo _l('download'); ?></a></li>
                  <li>
                     <a href="<?php echo admin_url('booking/quotes/pdf/'.$quote->id.'?print=true'); ?>" target="_blank">
                     <?php echo _l('print'); ?>
                     </a>
                  </li>
               </ul>
            </div>
            <a href="#" class="btn btn-default btn-with-tooltip" data-target="#quote_send_to_customer" data-toggle="modal"><span data-toggle="tooltip" class="btn-with-tooltip" data-title="<?php echo _l('quote_send_to_email'); ?>" data-placement="bottom"><i class="fa fa-envelope"></i></span></a>
            <div class="btn-group ">
               <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <?php echo _l('more'); ?> <span class="caret"></span>
               </button>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                     <a href="<?php echo site_url('booking/quote/index/'.$quote->id .'/'.$quote->hash); ?>" target="_blank"><?php echo _l('quote_view'); ?></a>
                  </li>
                  <?php hooks()->do_action('after_quote_view_as_client_link', $quote); ?>
                  <?php if(!empty($quote->open_till) && date('Y-m-d') < $quote->open_till && ($quote->status == 4 || $quote->status == 1) && is_quotes_expiry_reminders_enabled()) { ?>
                  <li>
                     <a href="<?php echo admin_url('booking/quotes/send_expiry_reminder/'.$quote->id); ?>"><?php echo _l('send_expiry_reminder'); ?></a>
                  </li>
                  <?php } ?>
                  <li>
                     <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                  </li>
                  <?php if(has_permission('quotes','','create')){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'booking/quotes/copy/'.$quote->id; ?>"><?php echo _l('quote_copy'); ?></a>
                  </li>
                  <?php } ?>
                  <?php if($quote->estimate_id == NULL && $quote->invoice_id == NULL){ ?>
                  <?php foreach($quote_statuses as $status){
                     if(has_permission('quotes','','edit')){
                      if($quote->status != $status){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'booking/quotes/mark_action_status/'.$status.'/'.$quote->id; ?>"><?php echo _l('quote_mark_as',format_quote_status($status,'',false)); ?></a>
                  </li>
                  <?php
                     } } } ?>
                  <?php } ?>
                  <?php if(!empty($quote->signature) && has_permission('quotes','','delete')){ ?>
                  <li>
                     <a href="<?php echo admin_url('booking/quotes/clear_signature/'.$quote->id); ?>" class="_delete">
                     <?php echo _l('clear_signature'); ?>
                     </a>
                  </li>
                  <?php } ?>
                  <?php if(has_permission('quotes','','delete')){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'booking/quotes/delete/'.$quote->id; ?>" class="text-danger delete-text _delete"><?php echo _l('quote_delete'); ?></a>
                  </li>
                  <?php } ?>
               </ul>
            </div>
            <?php if($quote->estimate_id == NULL && $quote->invoice_id == NULL){ ?>
            <?php if(has_permission('estimates','','create') || has_permission('invoices','','create')){ ?>
            <div class="btn-group" style="display:none;">
               <button type="button" class="btn btn-success dropdown-toggle<?php if($quote->rel_type == 'customer' && total_rows(db_prefix().'clients',array('active'=>0,'userid'=>$quote->rel_id)) > 0){echo ' disabled';} ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <?php echo _l('quote_convert'); ?> <span class="caret"></span>
               </button>
               <ul class="dropdown-menu dropdown-menu-right">
                  <?php
                     $disable_convert = false;
                     $not_related = false;

                     if($quote->rel_type == 'lead'){
                      if(total_rows(db_prefix().'clients',array('leadid'=>$quote->rel_id)) == 0){
                       $disable_convert = true;
                       $help_text = 'quote_convert_to_lead_disabled_help';
                     }
                     } else if(empty($quote->rel_type)){
                     $disable_convert = true;
                     $help_text = 'quote_convert_not_related_help';
                     }
                     ?>
                  <?php if(has_permission('estimates','','create')){ ?>
                  <li <?php if($disable_convert){ echo 'data-toggle="tooltip" title="'._l($help_text,_l('quote_convert_estimate')).'"';} ?>><a href="#" <?php if($disable_convert){ echo 'style="cursor:not-allowed;" onclick="return false;"';} else {echo 'data-template="estimate" onclick="quote_convert_template(this); return false;"';} ?>><?php echo _l('quote_convert_estimate'); ?></a></li>
                  <?php } ?>
                  <?php if(has_permission('invoices','','create')){ ?>
                  <li <?php if($disable_convert){ echo 'data-toggle="tooltip" title="'._l($help_text,_l('quote_convert_invoice')).'"';} ?>><a href="#" <?php if($disable_convert){ echo 'style="cursor:not-allowed;" onclick="return false;"';} else {echo 'data-template="invoice" onclick="quote_convert_template(this); return false;"';} ?>><?php echo _l('quote_convert_invoice'); ?></a></li>
                  <?php } ?>
               </ul>
            </div>
            <?php } ?>
            <?php } else {
               if($quote->estimate_id != NULL){
                echo '<a href="'.admin_url('estimates/list_estimates/'.$quote->estimate_id).'" class="btn btn-info">'.format_estimate_number($quote->estimate_id).'</a>';
               } else {
                echo '<a href="'.admin_url('invoices/list_invoices/'.$quote->invoice_id).'" class="btn btn-info">'.format_invoice_number($quote->invoice_id).'</a>';
               }
               } ?>
         </div>
      </div>
      <div class="clearfix"></div>
      <hr class="hr-panel-heading" />
      <div class="row">
         <div class="col-md-12">
            <div class="tab-content">
               <div role="tabpanel" class="tab-pane active" id="tab_quote">
                  <div class="row mtop10">
                     <?php if($quote->status == 3 && !empty($quote->acceptance_firstname) && !empty($quote->acceptance_lastname) && !empty($quote->acceptance_email)){ ?>
                     <div class="col-md-12">
                        <div class="alert alert-info">
                           <?php echo _l('accepted_identity_info',array(
                              _l('quote_lowercase'),
                              '<b>'.$quote->acceptance_firstname . ' ' . $quote->acceptance_lastname . '</b> (<a href="mailto:'.$quote->acceptance_email.'">'.$quote->acceptance_email.'</a>)',
                              '<b>'. _dt($quote->acceptance_date).'</b>',
                              '<b>'.$quote->acceptance_ip.'</b>'.(is_admin() ? '&nbsp;<a href="'.admin_url('booking/quotes/clear_acceptance_info/'.$quote->id).'" class="_delete text-muted" data-toggle="tooltip" data-title="'._l('clear_this_information').'"><i class="fa fa-remove"></i></a>' : '')
                              )); ?>
                        </div>
                     </div>
                     <?php } ?>
                     <div class="col-md-6">
                        <h4 class="bold">
                           <?php
                              $tags = get_tags_in($quote->id,'quote');
                              if(count($tags) > 0){
                               echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="'.html_escape(implode(', ',$tags)).'"></i>';
                              }
                              ?>
                           <a href="<?php echo admin_url('booking/quotes/quote/'.$quote->id); ?>">
                           <span id="quote-number">
                           <?php echo format_quote_number($quote->id); ?>
                           </span>
                           </a>
                        </h4>
                        <h5 class="bold mbot15 font-medium"><a href="<?php echo admin_url('booking/quotes/quote/'.$quote->id); ?>"><?php echo $quote->subject; ?></a></h5>
                        <address>
                           <?php echo format_organization_info(); ?>
                        </address>
                     </div>
                     <div class="col-md-6 text-right">
                        <address>
                           <span class="bold"><?php echo _l('quote_to'); ?>:</span><br />
                           <?php echo format_quote_info($quote,'admin'); ?>
                        </address>
                     </div>
                  </div>
                  <hr class="hr-panel-heading" />
                  <?php
                     if(count($quote->attachments) > 0){ ?>
                  <p class="bold"><?php echo _l('quote_files'); ?></p>
                  <?php foreach($quote->attachments as $attachment){
                     $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                     if(!empty($attachment['external'])){
                        $attachment_url = $attachment['external_link'];
                     }
                     ?>
                  <div class="mbot15 row" data-attachment-id="<?php echo $attachment['id']; ?>">
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
                        <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $quote->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="fa <?php echo $icon; ?>" aria-hidden="true"></i></a>
                        <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                        <a href="#" class="text-danger" onclick="delete_quote_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                        <?php } ?>
                     </div>
                  </div>
                  <?php } ?>
                  <?php } ?>
                  <div class="clearfix"></div>
                  <?php if(isset($quote_merge_fields)){ ?>
                  <p class="bold text-right"><a href="#" onclick="slideToggle('.avilable_merge_fields'); return false;"><?php echo _l('available_merge_fields'); ?></a></p>
                  <hr class="hr-panel-heading" />
                  <div class="hide avilable_merge_fields mtop15">
                     <div class="row">
                        <div class="col-md-12">
                           <ul class="list-group">
                              <?php
                                 foreach($quote_merge_fields as $field){
                                    foreach($field as $f){
                                      echo '<li class="list-group-item"><b>'.$f['name'].'</b> <a href="#" class="pull-right" onclick="insert_quote_merge_field(this); return false;">'.$f['key'].'</a></li>';
                                   }
                                }
                             ?>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <?php } ?>
                  <div class="editable quote tc-content" id="quote_content_area" style="border:1px solid #d2d2d2;min-height:70px;border-radius:4px;">
                     <?php if(empty($quote->content)){
                        echo '<span class="text-danger text-uppercase mtop15 editor-add-content-notice"> ' . _l('click_to_add_content') . '</span>';
                        } else {
                        echo $quote->content;
                        }
                        ?>
                  </div>
                      <?php if(!empty($quote->signature)) { ?>
                        <div class="row mtop25">
                           <div class="col-md-6 col-md-offset-6 text-right">
                              <p class="bold"><?php echo _l('document_customer_signature_text'); ?>
                                 <?php if(has_permission('quotes','','delete')){ ?>
                                 <a href="<?php echo admin_url('booking/quotes/clear_signature/'.$quote->id); ?>" data-toggle="tooltip" title="<?php echo _l('clear_signature'); ?>" class="_delete text-danger">
                                 <i class="fa fa-remove"></i>
                                 </a>
                                 <?php } ?>
                              </p>
                              <div class="pull-right">
                                 <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_upload_path_by_type('quote').$quote->id.'/'.$quote->signature)); ?>" class="img-responsive" alt="">
                              </div>
                           </div>
                        </div>
                        <?php } ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_comments">
                  <div class="row quote-comments mtop15">
                     <div class="col-md-12">
                        <div id="quote-comments"></div>
                        <div class="clearfix"></div>
                        <textarea name="content" id="comment" rows="4" class="form-control mtop15 quote-comment"></textarea>
                        <button type="button" class="btn btn-info mtop10 pull-right" onclick="add_quote_comment();"><?php echo _l('quote_add_comment'); ?></button>
                     </div>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_notes">
                  <?php echo form_open(admin_url('booking/quotes/add_note/'.$quote->id),array('id'=>'sales-notes','class'=>'quote-notes-form')); ?>
                  <?php echo render_textarea('description'); ?>
                  <div class="text-right">
                     <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('estimate_add_note'); ?></button>
                  </div>
                  <?php echo form_close(); ?>
                  <hr />
                  <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
                  <?php
                     $this->load->view('admin/includes/emails_tracking',array(
                       'tracked_emails'=>
                       get_tracked_emails($quote->id, 'quote'))
                       );
                     ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_tours">
                  <?php init_relation_tours_table(array( 'data-new-rel-id'=>$quote->id,'data-new-rel-type'=>'quote')); ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_reminders">
                  <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-quote-<?php echo $quote->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('quote_set_reminder_title'); ?></a>
                  <hr />
                  <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified')), 'reminders'); ?>
                  <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$quote->id,'name'=>'quote','members'=>$members,'reminder_title'=>_l('quote_set_reminder_title'))); ?>
               </div>
               <div role="tabpanel" class="tab-pane ptop10" id="tab_views">
                  <?php
                     $views_activity = get_views_tracking('quote',$quote->id);
                       if(count($views_activity) === 0) {
                     echo '<h4 class="no-margin">'._l('not_viewed_yet',_l('quote_lowercase')).'</h4>';
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
</div>
<?php $this->load->view('admin/quotes/send_quote_to_email_template'); ?>
<script>
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();
     // defined in manage quotes
     quote_id = '<?php echo $quote->id; ?>';
     init_quote_editor();
</script>
