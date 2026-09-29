<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="quote-wrapper">
   <?php
      ob_start();
      $items = get_items_table_data($quote, 'quote')
      ->add_table_class('no-margin')
      ->set_headings('estimate');

      echo $items->table();
      ?>
   <div class="row mtop15">
      <div class="col-md-6 col-md-offset-6">
         <table class="table text-right">
            <tbody>
               <tr id="subtotal">
                  <td><span class="bold"><?php echo _l('estimate_subtotal'); ?></span>
                  </td>
                  <td class="subtotal">
                     <?php echo app_format_money($quote->subtotal, $quote->currency_name); ?>
                  </td>
               </tr>
               <?php if(is_sale_discount_applied($quote)){ ?>
               <tr>
                  <td>
                     <span class="bold"><?php echo _l('estimate_discount'); ?>
                     <?php if(is_sale_discount($quote,'percent')){ ?>
                     (<?php echo app_format_number($quote->discount_percent,true); ?>%)
                     <?php } ?></span>
                  </td>
                  <td class="discount">
                     <?php echo '-' . app_format_money($quote->discount_total, $quote->currency_name); ?>
                  </td>
               </tr>
               <?php } ?>
               <?php
                  foreach($items->taxes() as $tax){
                   echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('.app_format_number($tax['taxrate']).'%)</td><td>'.app_format_money($tax['total_tax'], $quote->currency_name).'</td></tr>';
                  }
                  ?>
               <?php if((int)$quote->adjustment != 0){ ?>
               <tr>
                  <td>
                     <span class="bold"><?php echo _l('estimate_adjustment'); ?></span>
                  </td>
                  <td class="adjustment">
                     <?php echo app_format_money($quote->adjustment, $quote->currency_name); ?>
                  </td>
               </tr>
               <?php } ?>
               <tr>
                  <td><span class="bold"><?php echo _l('estimate_total'); ?></span>
                  </td>
                  <td class="total">
                     <?php echo app_format_money($quote->total, $quote->currency_name); ?>
                  </td>
               </tr>
            </tbody>
         </table>
      </div>
   </div>
   <?php
      if(get_option('total_to_words_enabled') == 1){ ?>
   <div class="col-md-12 text-center quote-html-total-to-words">
      <p class="bold"><?php echo  _l('num_word').': '.$this->numberword->convert($quote->total,$quote->currency_name); ?></p>
   </div>
   <?php }
      $items = ob_get_contents();
      ob_end_clean();
      $quote->content = str_replace('{quote_items}',$items,$quote->content);
      ?>
   <div class="mtop15 preview-top-wrapper">
      <div class="row">
         <div class="col-md-3">
            <div class="mbot30">
               <div class="quote-html-logo">
                  <?php echo get_dark_company_logo(); ?>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
      </div>
      <div class="top" data-sticky data-sticky-class="preview-sticky-header">
         <div class="container preview-sticky-container">
            <div class="row">
               <div class="col-md-12">
                  <div class="pull-left">
                     <h4 class="bold no-mtop quote-html-number"># <?php echo format_quote_number($quote->id); ?><br />
                        <small class="quote-html-subject"><?php echo $quote->subject; ?></small>
                     </h4>
                  </div>
                  <div class="visible-xs">
                     <div class="clearfix"></div>
                  </div>
                  <?php if(($quote->status != 2 && $quote->status != 3)){
                     if(!empty($quote->open_till) && date('Y-m-d',strtotime($quote->open_till)) < date('Y-m-d')){
                       echo '<span class="warning-bg content-view-status">'._l('quote_expired').'</span>';
                     } else { ?>
                  <?php if($identity_confirmation_enabled == '1'){ ?>
                  <button type="button" id="accept_action" class="btn btn-success pull-right action-button mleft5">
                  <i class="fa fa-check"></i> <?php echo _l('quote_accept_info'); ?>
                  </button>
                  <?php } else { ?>
                  <?php echo form_open($this->uri->uri_string()); ?>
                  <button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" class="btn btn-success pull-right action-button mleft5"><i class="fa fa-check"></i> <?php echo _l('quote_accept_info'); ?></button>
                  <?php echo form_hidden('action','accept_quote'); ?>
                  <?php echo form_close(); ?>
                  <?php } ?>
                  <?php echo form_open($this->uri->uri_string()); ?>
                  <button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" class="btn btn-default pull-right action-button mleft5"><i class="fa fa-remove"></i> <?php echo _l('quote_decline_info'); ?></button>
                  <?php echo form_hidden('action','decline_quote'); ?>
                  <?php echo form_close(); ?>
                  <?php } ?>
                  <!-- end expired quote -->
                  <?php } else {
                     if($quote->status == 2){
                       echo '<span class="danger-bg content-view-status">'._l('quote_status_declined').'</span>';
                     } else if($quote->status == 3){
                       echo '<span class="success-bg content-view-status">'._l('quote_status_accepted').'</span>';
                     }
                     } ?>
                  <?php echo form_open($this->uri->uri_string()); ?>
                  <button type="submit" class="btn btn-default pull-right action-button mleft5"><i class="fa-regular fa-file-pdf"></i> <?php echo _l('clients_invoice_html_btn_download'); ?></button>
                  <?php echo form_hidden('action','quote_pdf'); ?>
                  <?php echo form_close(); ?>
                  <?php if(is_client_logged_in() && has_contact_permission('quotes')){ ?>
                  <a href="<?php echo site_url('clients/quotes/'); ?>" class="btn btn-default mleft5 pull-right action-button go-to-portal">
                  <?php echo _l('client_go_to_dashboard'); ?>
                  </a>
                  <?php } ?>
                  <div class="clearfix"></div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-md-8 quote-left">
         <div class="panel_s mtop20">
            <div class="panel-body quote-content tc-content padding-30">
               <?php echo $quote->content; ?>
            </div>
         </div>
      </div>
      <div class="col-md-4 quote-right">
         <div class="inner mtop20 quote-html-tabs">
            <ul class="nav nav-tabs nav-tabs-flat mbot15" role="tablist">
               <li role="presentation" class="<?php if(!$this->input->get('tab') || $this->input->get('tab') === 'summary'){echo 'active';} ?>">
                  <a href="#summary" aria-controls="summary" role="tab" data-toggle="tab">
                  <i class="fa fa-file-text-o" aria-hidden="true"></i> <?php echo _l('summary'); ?></a>
               </li>
               <?php if($quote->allow_comments == 1){ ?>
               <li role="presentation" class="<?php if($this->input->get('tab') === 'discussion'){echo 'active';} ?>">
                  <a href="#discussion" aria-controls="discussion" role="tab" data-toggle="tab">
                  <i class="fa fa-commenting-o" aria-hidden="true"></i> <?php echo _l('discussion'); ?>
                  </a>
               </li>
               <?php } ?>
            </ul>
            <div class="tab-content">
               <div role="tabpanel" class="tab-pane<?php if(!$this->input->get('tab') || $this->input->get('tab') === 'summary'){echo ' active';} ?>" id="summary">
                  <address class="quote-html-company-info">
                     <?php echo format_organization_info(); ?>
                  </address>
                  <hr />
                  <p class="bold quote-html-information">
                     <?php echo _l('quote_information'); ?>
                  </p>
                  <address class="no-margin quote-html-info">
                     <?php echo format_quote_info($quote, 'html'); ?>
                  </address>
                  <div class="row mtop20">
                     <?php if($quote->total != 0){ ?>
                     <div class="col-md-12 quote-html-total">
                        <h4 class="bold mbot30"><?php echo _l('quote_total_info',app_format_money($quote->total, $quote->currency_name)); ?></h4>
                     </div>
                     <?php } ?>
                     <div class="col-md-4 text-muted quote-status">
                        <?php echo _l('quote_status'); ?>
                     </div>
                     <div class="col-md-8 quote-status">
                        <?php echo format_quote_status($quote->status,'', false); ?>
                     </div>
                     <div class="col-md-4 text-muted quote-date">
                        <?php echo _l('quote_date'); ?>
                     </div>
                     <div class="col-md-8 quote-date">
                        <?php echo _d($quote->date); ?>
                     </div>
                     <?php if(!empty($quote->open_till)){ ?>
                     <div class="col-md-4 text-muted quote-open-till">
                        <?php echo _l('quote_open_till'); ?>
                     </div>
                     <div class="col-md-8 quote-open-till">
                        <?php echo _d($quote->open_till); ?>
                     </div>
                     <?php } ?>
                  </div>
                  <?php if(count($quote->attachments) > 0 && $quote->visible_attachments_to_customer_found == true){ ?>
                  <div class="quote-attachments">
                     <hr />
                     <p class="bold mbot15"><?php echo _l('quote_files'); ?></p>
                     <?php foreach($quote->attachments as $attachment){
                        if($attachment['visible_to_customer'] == 0){continue;}
                        $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                        if(!empty($attachment['external'])){
                          $attachment_url = $attachment['external_link'];
                        }
                        ?>
                     <div class="col-md-12 row mbot15">
                        <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                        <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a>
                     </div>
                     <?php } ?>
                  </div>
                  <?php } ?>
               </div>
               <?php if($quote->allow_comments == 1){ ?>
               <div role="tabpanel" class="tab-pane<?php if($this->input->get('tab') === 'discussion'){echo ' active';} ?>" id="discussion">
                  <?php echo form_open($this->uri->uri_string()) ;?>
                  <div class="quote-comment">
                     <textarea name="content" rows="4" class="form-control"></textarea>
                     <button type="submit" class="btn btn-info mtop10 pull-right" data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('quote_add_comment'); ?></button>
                     <?php echo form_hidden('action','quote_comment'); ?>
                  </div>
                  <?php echo form_close(); ?>
                  <div class="clearfix"></div>
                  <?php
                     $quote_comments = '';
                     foreach ($comments as $comment) {
                      $quote_comments .= '<div class="quote_comment mtop10 mbot20" data-commentid="' . $comment['id'] . '">';
                      if($comment['staffid'] != 0){
                        $quote_comments .= staff_profile_image($comment['staffid'], array(
                          'staff-profile-image-small',
                          'media-object img-circle pull-left mright10'
                        ));
                      }
                      $quote_comments .= '<div class="media-body valign-middle">';
                      $quote_comments .= '<div class="mtop5">';
                      $quote_comments .= '<b>';
                      if($comment['staffid'] != 0){
                        $quote_comments .= get_staff_full_name($comment['staffid']);
                      } else {
                        $quote_comments .= _l('is_customer_indicator');
                      }
                      $quote_comments .= '</b>';
                      $quote_comments .= ' - <small class="mtop10 text-muted">' . time_ago($comment['dateadded']) . '</small>';
                      $quote_comments .= '</div>';
                      $quote_comments .= '<br />';
                      $quote_comments .= check_for_links($comment['content']) . '<br />';
                      $quote_comments .= '</div>';
                      $quote_comments .= '</div>';
                     }
                     echo $quote_comments; ?>
               </div>
               <?php } ?>
            </div>
            <div style="margin-top:80px;"></div>
         </div>
      </div>
   </div>
</div>
<?php
   if($identity_confirmation_enabled == '1'){
        get_template_part('identity_confirmation_form',array('formData'=>form_hidden('action','accept_quote')));
   }
   ?>
<script>
   $(function(){
     new Sticky('[data-sticky]');
     $(".quote-left table").wrap("<div class='table-responsive'></div>");
         // Create lightbox for quote content images
         $('.quote-content img').wrap( function(){ return '<a href="' + $(this).attr('src') + '" data-lightbox="quote"></a>'; });
     });
</script>
</div>
