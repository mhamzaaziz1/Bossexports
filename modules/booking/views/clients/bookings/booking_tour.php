<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="tour">
   <div class="row">
      <div class="col-md-12">
         <div class="row">
            <div class="col-md-9">
               <h3 class="no-mbot bold pull-left">
                  <?php if(
                     $booking->settings->edit_tours == 1 && 
                     $view_tour->is_added_from_contact == 1 && 
                     $view_tour->addedfrom == get_contact_user_id() &&
                     $view_tour->billed == 0
                     ){ ?>
                  <a href="<?php echo site_url('clients/booking/'.$booking->id.'?group=edit_tour&tourid='.$view_tour->id); ?>">
                  <i class="fa-regular fa-pen-to-square"></i>
                  </a>
                  <?php } ?> <?php echo $view_tour->name; ?>
               </h3>
               <div class="clearfix"></div>
               <div class="tour-info pull-left no-p-left">
                  <h5 class="no-margin"><i class="fa fa-bolt"></i>
                     <?php echo _l('tour_single_priority'); ?>: <?php echo tour_priority($view_tour->priority); ?>
                  </h5>
               </div>
               <div class="text-success tour-info pull-left">
                  <h5 class="no-margin"><i class="fa fa-hourglass-start"></i>
                     <?php echo _l('tour_single_start_date'); ?>: <?php echo _d($view_tour->startdate); ?>
                  </h5>
               </div>
               <div class="tour-info pull-left <?php if(!$view_tour->status != Tours_model::STATUS_COMPLETE){echo ' text-danger'; }else{echo 'text-info';} ?><?php if(!$view_tour->duedate){ echo ' hide';} ?>">
                  <h5 class="no-margin"><i class="fa fa-hourglass-end"></i>
                     <?php echo _l('tour_single_due_date'); ?>: <?php echo _d($view_tour->duedate); ?>
                  </h5>
               </div>
               <?php if($view_tour->status == Tours_model::STATUS_COMPLETE){ ?>
               <div class="pull-left tour-info text-success">
                  <h5 class="no-margin"><i class="fa fa-check"></i>
                     <?php echo _l('tour_single_finished'); ?>: <?php echo _dt($view_tour->datefinished); ?>
                  </h5>
               </div>
               <?php } ?>
               <?php if($booking->settings->view_tour_total_logged_time == 1){ ?>
               <div class="pull-left tour-info">
                  <h5 class="no-margin"><i class="fa fa-clock-o"></i>
                     <?php echo _l('tour_total_logged_time'); ?> <?php echo seconds_to_time_format($this->tours_model->calc_tour_total_time($view_tour->id)); ?>
                  </h5>
               </div>
               <?php } ?>
               <?php if($view_tour->billed == 1){ ?>
               <div class="clearfix"></div>
               <p class="no-mbot mtop15"><?php echo _l('tour_is_billed',format_invoice_number($view_tour->invoice_id)); ?></p>
               <?php } ?>
            </div>
            <div class="col-md-3">
               <span class="tour-single-status pull-right mright5 mtop15"><?php echo format_tour_status($view_tour->status); ?></span>
            </div>
         </div>
         <?php if($booking->settings->view_team_members == 1){ ?>
         <div class="clearfix"></div>
         <hr />
         <div class="row mbot20">
            <div class="col-md-3">
               <i class="fa fa-user-o"></i> <span class="bold"><?php echo _l('tour_single_assignees'); ?></span>
            </div>
            <div class="col-md-9" id="assignees">
               <?php
                  $_assignees = '';
                  foreach ($view_tour->assignees as $assignee) {
                    $_assignees .= '
                    <div data-toggle="tooltip" class="pull-left mleft5 tour-user" data-title="'.get_staff_full_name($assignee['assigneeid']).'">'
                    .staff_profile_image($assignee['assigneeid'], array(
                      'staff-profile-image-small'
                    )) .'</div>';
                  }
                  if ($_assignees == '') {
                    $_assignees = '<div class="tour-connectors-no-indicator display-block">'._l('tour_no_assignees').'</div>';
                  }
                  echo $_assignees;
                  ?>
            </div>
         </div>
         <?php } ?>
         <?php if($booking->settings->view_tour_checklist_items == 1){ ?>
         <?php if(count($view_tour->checklist_items) > 0){ ?>
         <hr />
         <h4 class="bold mbot15"><?php echo _l('tour_checklist_items'); ?></h4>
         <?php } ?>
         <?php foreach($view_tour->checklist_items as $list){ ?>
         <p class="<?php if($list['finished'] == 1){echo 'line-throught';} ?>">
            <span class="tour-checklist-indicator <?php if($list['finished'] == 1){echo 'text-success';} else{echo 'text-muted';} ?>"><i class="fa fa-check"></i></span>&nbsp;
            <?php echo $list['description']; ?>
         </p>
         <?php } ?>
         <?php } ?>
         <?php
            $custom_fields = get_custom_fields('tours',array('show_on_client_portal'=>1));
            if(count($custom_fields) > 0){ ?>
         <div class="row">
            <?php foreach($custom_fields as $field){ ?>
            <?php $value = get_custom_field_value($view_tour->id,$field['id'],'tours');
               if($value == ''){continue;} $custom_fields_found = true;?>
            <div class="col-md-9">
               <span class="bold"><?php echo ucfirst($field['name']); ?></span>
               <br />
               <div class="text-left">
                  <?php echo $value; ?>
               </div>
            </div>
            <?php } ?>
            <?php
               // Add separator if custom fields found for output
               if(isset($custom_fields_found)){?>
            <div class="clearfix"></div>
            <hr />
            <?php } ?>
         </div>
         <?php } ?>
         <?php if($booking->settings->view_tour_attachments == 1){ ?>
         <?php
            $attachments_data = array();
            $comments_attachments = array();
            $i = 1;
            $show_more_link_tour_attachments = hooks()->apply_filters('show_more_link_tour_attachments_customers_area', 3);
            if(count($view_tour->attachments) > 0){ ?>
         <hr />
         <div class="row tour_attachments_wrapper">
            <div class="col-md-12">
               <h4 class="bold font-medium"><?php echo _l('tour_view_attachments'); ?></h4>
               <div class="row">
                  <?php foreach($view_tour->attachments as $attachment){
                  ob_start(); ?>
                  <div class="col-md-4 tour-attachment-col<?php if($i > $show_more_link_tour_attachments){echo ' tour-attachment-col-more';} ?>"
                     data-num="<?php echo $i; ?>"
                     <?php if($i > $show_more_link_tour_attachments){echo 'style="display:none;"';} ?>>
                     <ul class="list-unstyled">
                        <li class="mbot10 tour-attachment">
                           <div class="mbot10 pull-right tour-attachment-user">
                              <?php
                                 echo _l('booking_file_uploaded_by') . ' ' . (
                                    $attachment['staffid'] != 0
                                    ? get_staff_full_name($attachment['staffid'])
                                    : get_contact_full_name($attachment['contact_id'])
                                 );
                                 ?>
                              <?php if(get_option('allow_contact_to_delete_files') == 1 && $attachment['contact_id'] == get_contact_user_id()){ ?>
                              <a href="<?php echo site_url('clients/delete_file/'.$attachment['id'].'/tour?booking_id='.$booking->id); ?>" class="text-danger _delete pull-right"><i class="fa fa-remove"></i></a>
                              <?php } ?>
                           </div>
                           <?php
                              $externalPreview = false;
                              $is_image = false;
                              $path = get_upload_path_by_type('tour') . $view_tour->id . '/'. $attachment['file_name'];
                              $href_url = site_url('download/file/tourattachment/'. $attachment['attachment_key']);
                              $isHtml5Video = is_html5_video($path);
                              if(empty($attachment['external'])){
                                $is_image = is_image($path);
                                $img_url = site_url('download/preview_image?path='.protected_file_url_by_path($path,true).'&type='.$attachment['filetype']);
                              } else if((!empty($attachment['thumbnail_link']) || !empty($attachment['external']))
                               && !empty($attachment['thumbnail_link'])){
                               $is_image = true;
                               $img_url = optimize_dropbox_thumbnail($attachment['thumbnail_link']);
                               $externalPreview = $img_url;
                               $href_url = $attachment['external_link'];
                              } else if(!empty($attachment['external']) && empty($attachment['thumbnail_link'])) {
                               $href_url = $attachment['external_link'];
                              }
                              if(!empty($attachment['external']) && $attachment['external'] == 'dropbox' && $is_image){ ?>
                           <a href="<?php echo $href_url; ?>" target="_blank" class="open-in-external" data-toggle="tooltip" data-title="<?php echo _l('open_in_dropbox'); ?>"><i class="fa fa-dropbox" aria-hidden="true"></i></a>
                           <?php } else if(!empty($attachment['external']) && $attachment['external'] == 'gdrive'){ ?>
                           <a href="<?php echo $href_url; ?>" target="_blank" class="open-in-external" data-toggle="tooltip" data-title="<?php echo _l('open_in_google'); ?>"><i class="fa fa-google" aria-hidden="true"></i></a>
                           <?php }
                              ?>
                           <div class="<?php if($is_image){echo 'preview-image';}else if(!$isHtml5Video){echo 'tour-attachment-no-preview';} ?>">
                              <?php if(!$isHtml5Video){ ?>
                              <a href="<?php echo (!$externalPreview ? $href_url : $externalPreview); ?>" target="_blank"<?php if($is_image){ ?> data-lightbox="tour-attachment"<?php } ?> class="<?php if($isHtml5Video){echo 'video-preview';} ?>">
                                 <?php } ?>
                                 <?php if($is_image){ ?>
                                 <img src="<?php echo $img_url; ?>" class="img img-responsive">
                                 <?php } else if($isHtml5Video) { ?>
                                 <video width="100%" height="100%" src="<?php echo site_url('download/preview_video?path='.protected_file_url_by_path($path).'&type='.$attachment['filetype']); ?>" controls>
                                    Your browser does not support the video tag.
                                 </video>
                                 <?php } else { ?>
                                 <i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i> <?php echo $attachment['file_name']; ?>
                                 <?php } ?>
                                 <?php if(!$isHtml5Video){ ?>
                              </a>
                              <?php } ?>
                           </div>
                           <div class="clearfix"></div>
                        </li>
                     </ul>
                  </div>
                  <?php
                     $attachments_data[$attachment['id']] = ob_get_contents();
                              if($attachment['tour_comment_id'] != 0) {
                                 $comments_attachments[$attachment['tour_comment_id']][$attachment['id']] = $attachments_data[$attachment['id']];
                              }
                              ob_end_clean();
                              echo $attachments_data[$attachment['id']];
                              $i++;
                              ?>
                  <?php } ?>
               </div>
         <?php if(($i - 1) > $show_more_link_tour_attachments){ ?>
            <div id="show-more-less-tour-attachments-col">
               <a href="#" class="tour-attachments-more" onclick="slideToggle('.tour_attachments_wrapper .tour-attachment-col-more', tour_attachments_toggle); return false;"><?php echo _l('show_more'); ?></a>
               <a href="#" class="tour-attachments-less hide" onclick="slideToggle('.tour_attachments_wrapper .tour-attachment-col-more', tour_attachments_toggle); return false;"><?php echo _l('show_less'); ?></a>
            </div>
            <?php } ?>
            </div>
         </div>
         <?php } ?>
         <?php } ?>
         <?php if(!empty($view_tour->description)){ ?>
         <hr />
         <h4 class="bold"><?php echo _l('tour_view_description'); ?></h4>
         <div class="tc-content">
            <?php echo $view_tour->description; ?>
         </div>
         <?php } ?>
         <?php if($booking->settings->upload_files == 1){ ?>
         <hr />
         <?php echo form_open_multipart(site_url('clients/booking/'.$booking->id),array('class'=>'dropzone mtop15','id'=>'tour-file-upload')); ?>
         <input type="file" name="file" multiple class="hide"/>
         <input type="hidden" name="tour_id" value="<?php echo $view_tour->id; ?>" class="hide"/>
         <?php echo form_close(); ?>
         <div class="text-right mtop15">
            <button class="gpicker" data-on-pick="tourFileGoogleDriveSave">
            <i class="fa fa-google" aria-hidden="true"></i>
            <?php echo _l('choose_from_google_drive'); ?>
            </button>
            <div id="dropbox-chooser-tour"></div>
         </div>
         <?php } ?>
         <?php if($booking->settings->view_tour_comments == 1){ ?>
         <hr />
         <h4 class="bold mbot15"><?php echo _l('tour_view_comments'); ?></h4>
         <?php
            if($booking->settings->comment_on_tours == 1){
            echo form_open($this->uri->uri_string());
            echo form_hidden('action','new_tour_comment');
            echo form_hidden('tourid',$view_tour->id);
            ?>
         <textarea name="content" rows="5" class="form-control mtop15"></textarea>
         <button type="submit" class="btn btn-info mtop10 pull-right" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off"><?php echo _l('tour_single_add_new_comment'); ?></button>
         <div class="clearfix"></div>
         <?php echo form_close(); } ?>
         <?php
            if(count($view_tour->comments) > 0){
              echo '<div id="tour-comments">';
              foreach($view_tour->comments as $comment){ ?>
         <div class="mbot10 mtop10 tour-comment" data-commentid="<?php echo $comment['id']; ?>" id="comment_<?php echo $comment['id']; ?>">
            <?php if($comment['staffid'] != 0){ ?>
            <?php echo staff_profile_image($comment['staffid'], array(
               'staff-profile-image-small',
               'media-object img-circle pull-left mright10'
               )); ?>
            <?php } else { ?>
            <img src="<?php echo contact_profile_image_url($comment['contact_id']); ?>" class="client-profile-image-small media-object img-circle pull-left mright10">
            <?php } ?>
            <div class="media-body">
               <?php if($comment['staffid'] != 0){ ?>
               <span class="bold"><?php echo $comment['staff_full_name']; ?></span><br />
               <?php } else { ?>
               <span class="pull-left bold">
               <?php echo get_contact_full_name($comment['contact_id']); ?></span>
               <br />
               <?php } ?>
               <small class="mtop10 text-muted"><?php echo _dt($comment['dateadded']); ?></small><br />
               <?php if($comment['contact_id'] != 0){ ?>
               <?php
                  $comment_added = strtotime($comment['dateadded']);
                  $minus_1_hour = strtotime('-1 hours');
                  if(get_option('client_staff_add_edit_delete_tour_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_tour_comments_first_hour') == 1 && $comment_added >= $minus_1_hour)){ ?>
               <a href="#" onclick="remove_tour_comment(<?php echo $comment['id']; ?>); return false;" class="pull-right">
               <i class="fa fa-times text-danger"></i>
               </a>
               <a href="#" onclick="edit_tour_comment(<?php echo $comment['id']; ?>); return false;" class="pull-right mright5">
               <i class="fa-regular fa-pen-to-square"></i>
               </a>
               <div data-edit-comment="<?php echo $comment['id']; ?>" class="hide">
                  <textarea rows="5" class="form-control mtop10 mbot10"><?php echo clear_textarea_breaks($comment['content']); ?></textarea>
                  <button type="button" class="btn btn-info pull-right" onclick="save_edited_comment(<?php echo $comment['id']; ?>)">
                  <?php echo _l('submit'); ?>
                  </button>
                  <button type="button" class="btn btn-default pull-right mright5" onclick="cancel_edit_comment(<?php echo $comment['id']; ?>)">
                  <?php echo _l('cancel'); ?>
                  </button>
               </div>
               <?php } ?>
               <?php } ?>
               <div class="comment-content" data-comment-content="<?php echo $comment['id'] ;?>">
                  <?php
                     if($comment['file_id'] != 0 && $booking->settings->view_tour_attachments == 1){
                       $comment['content'] = str_replace('[tour_attachment]','<br />'.$attachments_data[$comment['file_id']],$comment['content']);
                       // Replace lightbox to prevent loading the image twice
                       $comment['content'] = str_replace('data-lightbox="tour-attachment"','data-lightbox="tour-attachment-comment-'.$comment['id'].'"',$comment['content']);
                     } else if(count($comment['attachments']) > 0 && isset($comments_attachments[$comment['id']])) {
                     $comment_attachments_html = '';
                     foreach($comments_attachments[$comment['id']] as $comment_attachment) {
                       $comment_attachments_html .= trim($comment_attachment);
                     }
                     $comment['content'] = str_replace('[tour_attachment]','<div class="clearfix"></div>'.$comment_attachments_html,$comment['content']);
                     // Replace lightbox to prevent loading the image twice
                     $comment['content'] = str_replace('data-lightbox="tour-attachment"','data-lightbox="tour-comment-files-'.$comment['id'].'"',$comment['content']);
                     $comment['content'] .='<div class="clearfix"></div>';
                     }
                     echo check_for_links($comment['content']); ?>
               </div>
            </div>
            <hr />
         </div>
         <?php }
            }
            ?>
      </div>
      <?php } ?>
   </div>
</div>
</div>
<script>
   var tour_comment_temp = window.location.href.split('#');
   if(tour_comment_temp[1]){
     var tour_comment_id = tour_comment_temp[tour_comment_temp.length-1];
     $('html,body').animate({
       scrollTop: $('#' + tour_comment_id).offset().top},
       'slow');
   }
</script>
