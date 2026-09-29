<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal-header tour-single-header" data-tour-single-id="<?php echo $tour->id; ?>" data-status="<?php echo $tour->status; ?>">
   <?php if($this->input->get('opened_from_lead_id')){ ?>
   <a href="#" onclick="init_lead(<?php echo $this->input->get('opened_from_lead_id'); ?>); return false;" class="back-to-from-tour color-white" data-placement="left" data-toggle="tooltip" data-title="<?php echo _l('back_to_lead'); ?>">
   <i class="fa fa-tty" aria-hidden="true"></i>
   </a>
   <?php } ?>
   <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
   <h4 class="modal-title"><?php echo $tour->name; ?></h4>
   <?php if($tour->billed == 1){ ?>
   <?php  echo '<p class="no-margin">'._l('tour_is_billed','<a href="'.admin_url('invoices/list_invoices/'.$tour->invoice_id).'" target="_blank" class="color-white">'.format_invoice_number($tour->invoice_id)). '</a></p>'; ?>
   <?php } ?>
   <?php if($tour->is_public == 0){ ?>
   <p class="no-margin color-white">
   <?php echo _l('tour_is_private'); ?>
      <?php if(has_permission('tours','','edit')) { ?> -
         <a href="#" class="color-white text-has-action" onclick="make_tour_public(<?php echo $tour->id; ?>); return false;">
            <?php echo _l('tour_view_make_public'); ?>
         </a>
      <?php } ?>
   </p>
   <?php } ?>
</div>
<div class="modal-body">
   <div class="row">
      <div class="col-md-8 tour-single-col-left">
         <?php if(total_rows(db_prefix().'tourstimers',array('end_time'=>NULL,'staff_id !='=>get_staff_user_id(),'tour_id'=>$tour->id)) > 0){
            $startedTimers = $this->tours_model->get_timers($tour->id,array('staff_id !='=>get_staff_user_id(),'end_time'=>NULL));

            $usersWorking = '';

            foreach($startedTimers as $t){
              $usersWorking .= '<b>' . get_staff_full_name($t['staff_id']) . '</b>, ';
            }

            $usersWorking = rtrim($usersWorking, ', ');
            ?>
         <p class="mbot20 info-block">
            <?php echo _l((count($startedTimers) == 1
               ? 'tour_users_working_on_tours_single'
               : 'tour_users_working_on_tours_multiple'),$usersWorking);
               ?>
         </p>
         <?php } ?>
         <?php if(!empty($tour->rel_id)){
            echo '<div class="tour-single-related-wrapper">';
            $tour_rel_data = get_relation_data($tour->rel_type,$tour->rel_id);
            $tour_rel_value = get_relation_values($tour_rel_data,$tour->rel_type);
            echo '<h4 class="bold font-medium mbot15">'._l('tour_single_related').': <a href="'.$tour_rel_value['link'].'" target="_blank">'.$tour_rel_value['name'].'</a>';
            if($tour->rel_type == 'booking' && $tour->milestone != 0){
             echo '<div class="mtop5 mbot20 font-normal">' . _l('tour_milestone') . ': ';
             $milestones = get_booking_milestones($tour->rel_id);
             if(has_permission('tours','','edit') && count($milestones) > 1){ ?>
         <span class="tour-single-menu tour-menu-milestones">
            <span class="trigger pointer manual-popover text-has-action">
            <?php echo $tour->milestone_name; ?>
            </span>
            <span class="content-menu hide">
               <ul>
                  <?php
                  foreach($milestones as $milestone){ ?>
                  <?php if($tour->milestone != $milestone['id']){ ?>
                  <li>
                     <a href="#" onclick="tour_change_milestone(<?php echo $milestone['id']; ?>,<?php echo $tour->id; ?>); return false;">
                     <?php echo $milestone['name']; ?>
                     </a>
                  </li>
                  <?php } ?>
                  <?php } ?>
               </ul>
            </span>
         </span>
         <?php } else {
            echo $tour->milestone_name;
            }
            echo '</div>';
            }
            echo '</h4>';
            echo '</div>';
            } ?>
         <div class="clearfix"></div>
         <?php if($tour->status != Tours_model::STATUS_COMPLETE && ($tour->current_user_is_assigned || has_permission('tours','','edit') || $tour->current_user_is_creator)){ ?>
         <p class="no-margin pull-left" style="<?php echo 'margin-'.(is_rtl() ? 'left' : 'right').':5px !important'; ?>">
            <a href="#" class="btn btn-info" id="tour-single-mark-complete-btn" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="mark_complete(<?php echo $tour->id; ?>); return false;" data-toggle="tooltip" title="<?php echo _l('tour_single_mark_as_complete'); ?>">
            <i class="fa fa-check"></i>
            </a>
         </p>
         <?php } else if($tour->status == Tours_model::STATUS_COMPLETE && ($tour->current_user_is_assigned || has_permission('tours','','edit') || $tour->current_user_is_creator)){ ?>
         <p class="no-margin pull-left" style="<?php echo 'margin-'.(is_rtl() ? 'left' : 'right').':5px !important'; ?>">
            <a href="#" class="btn btn-default" id="tour-single-unmark-complete-btn" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="unmark_complete(<?php echo $tour->id; ?>); return false;" data-toggle="tooltip" title="<?php echo _l('tour_unmark_as_complete'); ?>">
            <i class="fa fa-check"></i>
            </a>
         </p>
         <?php } ?>
         <?php if(has_permission('tours','','create') && count($tour->timesheets) > 0){ ?>
         <p class="no-margin pull-left mright5">
            <a href="#" class="btn btn-default mright5" data-toggle="tooltip" data-title="<?php echo _l('tour_statistics'); ?>" onclick="tour_tracking_stats(<?php echo $tour->id; ?>); return false;">
            <i class="fa fa-bar-chart"></i>
            </a>
         </p>
         <?php } ?>
         <p class="no-margin pull-left mright5">
            <a href="#" class="btn btn-default mright5" data-toggle="tooltip" data-title="<?php echo _l('tour_timesheets'); ?>"onclick="slideToggle('#tour_single_timesheets'); return false;">
            <i class="fa fa-th-list"></i>
            </a>
         </p>
         <?php if($tour->billed == 0){
            $is_assigned = $tour->current_user_is_assigned;
            if(!$this->tours_model->is_timer_started($tour->id)) { ?>
            <p class="no-margin pull-left"<?php if(!$is_assigned){ ?> data-toggle="tooltip" data-title="<?php echo _l('tour_start_timer_only_assignee'); ?>"<?php } ?>>
               <a href="#" class="mbot10 btn<?php if(!$is_assigned || $tour->status == Tours_model::STATUS_COMPLETE){echo ' disabled btn-default';}else {echo ' btn-success';} ?>" onclick="timer_action(this, <?php echo $tour->id; ?>); return false;">
               <i class="fa fa-clock-o"></i> <?php echo _l('tour_start_timer'); ?>
               </a>
            </p>
         <?php } else { ?>
         <p class="no-margin pull-left">
            <a href="#" data-toggle="popover" data-placement="<?php echo is_mobile() ? 'bottom' : 'right'; ?>" data-html="true" data-trigger="manual" data-title="<?php echo _l('note'); ?>" data-content='<?php echo render_textarea('timesheet_note'); ?><button type="button" onclick="timer_action(this, <?php echo $tour->id; ?>, <?php echo $this->tours_model->get_last_timer($tour->id)->id; ?>);" class="btn btn-info btn-xs"><?php echo _l('save'); ?></button>' class="btn mbot10 btn-danger<?php if(!$is_assigned){echo ' disabled';} ?>" onclick="return false;">
            <i class="fa fa-clock-o"></i> <?php echo _l('tour_stop_timer'); ?>
            </a>
         </p>
         <?php } ?>
         <?php } ?>
         <div class="clearfix"></div>
         <hr class="hr-10" />
         <div id="tour_single_timesheets" class="<?php if(!$this->session->flashdata('tour_single_timesheets_open')){echo 'hide';} ?>">
            <div class="table-responsive">
               <table class="table">
                  <thead>
                     <tr>
                        <th><?php echo _l('timesheet_user'); ?></th>
                        <th><?php echo _l('timesheet_start_time'); ?></th>
                        <th><?php echo _l('timesheet_end_time'); ?></th>
                        <th><?php echo _l('timesheet_time_spend'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                        $timers_found = false;
                        foreach($tour->timesheets as $timesheet){ ?>
                     <?php if(has_permission('tours','','edit') || has_permission('tours','','create') || has_permission('tours','','delete') || $timesheet['staff_id'] == get_staff_user_id()){
                        $timers_found = true; ?>
                     <tr>
                        <td>
                           <?php if($timesheet['note']){
                              echo '<i class="fa fa-comment" data-html="true" data-placement="right" data-toggle="tooltip" data-title="'.html_escape($timesheet['note']).'"></i>';
                              }
                              ?>
                           <a href="<?php echo admin_url('staff/profile/' . $timesheet['staff_id']); ?>" target="_blank">
                           <?php echo $timesheet['full_name']; ?></a>
                        </td>
                        <td><?php echo _dt($timesheet['start_time'], true); ?></td>
                        <td>
                           <?php
                              if($timesheet['end_time'] !== NULL){
                                 echo _dt($timesheet['end_time'],true);
                              } else {
                                 // Allow admins to stop forgotten timers by staff member
                                 if(!$tour->billed && is_admin()) {
                                 ?>
                                 <a href="#"
                                 data-toggle="popover"
                                 data-placement="bottom"
                                 data-html="true"
                                 data-trigger="manual"
                                 data-title="<?php echo _l('note'); ?>"
                                 data-content='<?php echo render_textarea('timesheet_note'); ?><button type="button" onclick="timer_action(this, <?php echo $tour->id; ?>, <?php echo $timesheet['id']; ?>, 1);" class="btn btn-info btn-xs"><?php echo _l('save'); ?></button>'
                                 class="text-danger"
                                 onclick="return false;">
                                    <i class="fa fa-clock-o"></i>
                                    <?php echo _l('tour_stop_timer'); ?>
                                 </a>
                           <?php
                              }
                           }
                           ?>
                        </td>
                        <td>
                           <?php
                              if(!$tour->billed){
                                   if(has_permission('tours','','delete') || (has_permission('bookings','','delete') && $tour->rel_type == 'booking') || $timesheet['staff_id'] == get_staff_user_id()){
                                    echo '<a href="'.admin_url('tours/delete_timesheet/'.$timesheet['id']).'" class="tour-single-delete-timesheet pull-right text-danger mtop5" data-tour-id="'.$tour->id.'"><i class="fa fa-remove"></i></a>';
                                 }
                              }
                              if($timesheet['time_spent'] == NULL){
                               echo _l('time_h').': '. seconds_to_time_format(time() - $timesheet['start_time']).'<br />';
                               echo _l('time_decimal').': '. sec2qty(time() - $timesheet['start_time']).'<br />';
                              } else {
                               echo _l('time_h').': '. seconds_to_time_format($timesheet['time_spent']).'<br />';
                               echo _l('time_decimal').': '. sec2qty($timesheet['time_spent']).'<br />';
                              }
                              ?>
                        </td>
                     </tr>
                     <?php } ?>
                     <?php } ?>
                     <?php if($timers_found == false){ ?>
                     <tr>
                        <td colspan="5" class="text-center bold"><?php echo _l('no_timers_found'); ?></td>
                     </tr>
                     <?php } ?>
                     <?php if($tour->billed == 0 && ($is_assigned || (count($tour->assignees) > 0 && is_admin())) && $tour->status != Tours_model::STATUS_COMPLETE){
                        ?>
                     <tr class="odd">
                        <td colspan="5">
                           <div class="timesheet-start-end-time">
                              <div class="col-md-6">
                                 <?php echo render_datetime_input('timesheet_start_time','tour_log_time_start'); ?>
                              </div>
                              <div class="col-md-6">
                                 <?php echo render_datetime_input('timesheet_end_time','tour_log_time_end'); ?>
                              </div>
                           </div>
                           <div class="timesheet-duration hide">
                              <div class="col-md-12">
                                 <i class="fa fa-question-circle pointer pull-left mtop2" data-toggle="popover" data-html="true" data-content="
                                    :15 - 15 <?php echo _l('minutes'); ?><br />
                                    2 - 2 <?php echo _l('hours'); ?><br />
                                    5:5 - 5 <?php echo _l('hours'); ?> & 5 <?php echo _l('minutes'); ?><br />
                                    2:50 - 2 <?php echo _l('hours'); ?> & 50 <?php echo _l('minutes'); ?><br />
                                    "></i>
                                 <?php echo render_input('timesheet_duration','booking_timesheet_time_spend','','text',array('placeholder'=>'HH:MM')); ?>
                              </div>
                           </div>
                           <div class="col-md-12 mbot15 mntop15">
                              <a href="#" class="timesheet-toggle-enter-type">
                              <span class="timesheet-duration-toggler-text switch-to">
                              <?php echo _l('timesheet_duration_instead'); ?>
                              </span>
                              <span class="timesheet-date-toggler-text hide ">
                              <?php echo _l('timesheet_date_instead'); ?>
                              </span>
                              </a>
                           </div>
                           <div class="col-md-12">
                              <div class="form-group">
                                 <label class="control-label">
                                 <?php echo _l('tour_single_log_user'); ?>
                                 </label>
                                 <br />
                                 <select name="single_timesheet_staff_id" class="selectpicker" data-width="100%">
                                    <?php foreach($tour->assignees as $assignee){
                                       if((!has_permission('tours','','create') && !has_permission('tours','','edit') && $assignee['assigneeid'] != get_staff_user_id()) || ($tour->rel_type == 'booking' && !has_permission('bookings','','edit') && $assignee['assigneeid'] != get_staff_user_id())){continue;}
                                       $selected = '';
                                       if($assignee['assigneeid'] == get_staff_user_id()){
                                        $selected = ' selected';
                                       }
                                       ?>
                                    <option<?php echo $selected; ?> value="<?php echo $assignee['assigneeid']; ?>" >
                                       <?php echo $assignee['full_name']; ?>
                                    </option>
                                    <?php } ?>
                                 </select>
                              </div>
                              <?php echo render_textarea('tour_single_timesheet_note','note'); ?>
                           </div>
                           <div class="col-md-12 text-right">
                              <?php
                                 $disable_button = '';
                                 if($this->tours_model->is_timer_started_for_tour($tour->id,array('staff_id'=>get_staff_user_id()))){
                                  $disable_button = 'disabled ';
                                  echo '<div class="text-right mbot15 text-danger">'. _l('add_tour_timer_started_warning') . '</div>';
                                 }
                                 ?>
                              <button <?php echo $disable_button; ?>data-tour-id="<?php echo $tour->id; ?>" class="btn btn-success tour-single-add-timesheet"><i class="fa fa-plus"></i> <?php echo _l('submit'); ?></button>
                           </div>
                        </td>
                     </tr>
                     <?php } ?>
                  </tbody>
               </table>
            </div>
            <hr />
         </div>
         <div class="clearfix"></div>
         <h4 class="th font-medium mbot15 pull-left"><?php echo _l('tour_view_description'); ?></h4>
         <?php if(has_permission('tours','','edit')){ ?><a href="#" onclick="edit_tour_inline_description(this,<?php echo $tour->id; ?>); return false;" class="pull-left mtop10 mleft5 font-medium-xs"><i class="fa-regular fa-pen-to-square"></i></a>
         <?php } ?>
         <div class="clearfix"></div>
         <?php if(!empty($tour->description)){
            echo '<div class="tc-content"><div id="tour_view_description">' .check_for_links($tour->description) .'</div></div>';
            } else {
            echo '<div class="no-margin tc-content tour-no-description" id="tour_view_description"><span class="text-muted">' . _l('tour_no_description') . '</span></div>';
            } ?>
         <div class="clearfix"></div>
         <hr />
         <a href="#" onclick="add_tour_checklist_item('<?php echo $tour->id; ?>', undefined, this); return false" class="mbot10 inline-block">
         <span class="new-checklist-item"><i class="fa fa-plus-circle"></i>
         <?php echo _l('add_checklist_item'); ?>
         </span>
         </a>
         <div class="form-group no-mbot checklist-templates-wrapper simple-bootstrap-select tour-single-checklist-templates<?php if(count($checklistTemplates) == 0){echo ' hide';}  ?>">
            <select id="checklist_items_templates" class="selectpicker checklist-items-template-select" data-none-selected-text="<?php echo _l('insert_checklist_templates') ?>" data-width="100%" data-live-search="true">
               <option value=""></option>
               <?php foreach($checklistTemplates as $chkTemplate){ ?>
               <option value="<?php echo $chkTemplate['id']; ?>">
                  <?php echo $chkTemplate['description']; ?>
               </option>
               <?php } ?>
            </select>
         </div>
         <div class="clearfix"></div>
         <p class="hide text-muted no-margin" id="tour-no-checklist-items"><?php echo _l('tour_no_checklist_items_found'); ?></p>
         <div class="row checklist-items-wrapper">
            <div class="col-md-12 ">
               <div id="checklist-items">
                  <?php $this->load->view('admin/tours/checklist_items_template',
                     array(
                       'tour_id'=>$tour->id,
                       'checklists'=>$tour->checklist_items)); ?>
               </div>
            </div>
            <div class="clearfix"></div>
         </div>
         <?php if(count($tour->attachments) > 0){ ?>
         <div class="row tour_attachments_wrapper">
            <div class="col-md-12" id="attachments">
               <hr />
               <h4 class="th font-medium mbot15"><?php echo _l('tour_view_attachments'); ?></h4>
               <div class="row">
                  <?php
                     $i = 1;
                     // Store all url related data here
                     $comments_attachments = array();
                     $attachments_data = array();
                     $show_more_link_tour_attachments = hooks()->apply_filters('show_more_link_tour_attachments', 2);
                     foreach($tour->attachments as $attachment){ ?>
                  <?php ob_start(); ?>
                  <div data-num="<?php echo $i; ?>" data-commentid="<?php echo $attachment['comment_file_id']; ?>" data-comment-attachment="<?php echo $attachment['tour_comment_id']; ?>" data-tour-attachment-id="<?php echo $attachment['id']; ?>" class="tour-attachment-col col-md-6<?php if($i > $show_more_link_tour_attachments){echo ' hide tour-attachment-col-more';} ?>">
                     <ul class="list-unstyled tour-attachment-wrapper" data-placement="right" data-toggle="tooltip" data-title="<?php echo $attachment['file_name']; ?>" >
                        <li class="mbot10 tour-attachment<?php if(strtotime($attachment['dateadded']) >= strtotime('-16 hours')){echo ' highlight-bg'; } ?>">
                           <div class="mbot10 pull-right tour-attachment-user">
                              <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                              <a href="#" class="pull-right" onclick="remove_tour_attachment(this,<?php echo $attachment['id']; ?>); return false;">
                              <i class="fa fa fa-times"></i>
                              </a>
                              <?php }
                                 $externalPreview = false;
                                 $is_image = false;
                                 $path = get_upload_path_by_type('tour') . $tour->id . '/'. $attachment['file_name'];
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
                              <a href="<?php echo $href_url; ?>" target="_blank" class="" data-toggle="tooltip" data-title="<?php echo _l('open_in_dropbox'); ?>"><i class="fa fa-dropbox" aria-hidden="true"></i></a>
                              <?php } else if(!empty($attachment['external']) && $attachment['external'] == 'gdrive'){ ?>
                              <a href="<?php echo $href_url; ?>" target="_blank" class="" data-toggle="tooltip" data-title="<?php echo _l('open_in_google'); ?>"><i class="fa fa-google" aria-hidden="true"></i></a>
                              <?php }
                                 if($attachment['staffid'] != 0){
                                   echo '<a href="'.admin_url('profile/'.$attachment['staffid']).'" target="_blank">'.get_staff_full_name($attachment['staffid']) .'</a> - ';
                                 } else if($attachment['contact_id'] != 0) {
                                   echo '<a href="'.admin_url('booking/clients/client/'.get_user_id_by_contact_id($attachment['contact_id']).'?contactid='.$attachment['contact_id']).'" target="_blank">'.get_contact_full_name($attachment['contact_id']) .'</a> - ';
                                 }
                                 echo '<span class="text-has-action" data-toggle="tooltip" data-title="'._dt($attachment['dateadded']).'">'.time_ago($attachment['dateadded']).'</span>';
                                 ?>
                           </div>
                           <div class="clearfix"></div>
                           <div class="<?php if($is_image){echo 'preview-image';}else if(!$isHtml5Video){echo 'tour-attachment-no-preview';} ?>">
                              <?php
                                 // Not link on video previews because on click on the video is opening new tab
                                 if(!$isHtml5Video){ ?>
                              <a href="<?php echo (!$externalPreview ? $href_url : $externalPreview); ?>" target="_blank"<?php if($is_image){ ?> data-lightbox="tour-attachment"<?php } ?> class="<?php if($isHtml5Video){echo 'video-preview';} ?>">
                                 <?php } ?>
                                 <?php if($is_image){ ?>
                                 <img src="<?php echo $img_url; ?>" class="img img-responsive">
                                 <?php } else if($isHtml5Video) { ?>
                                 <video width="100%" height="100%" src="<?php echo site_url('download/preview_video?path='.protected_file_url_by_path($path).'&type='.$attachment['filetype']); ?>" controls>
                                    Your browser does not support the video tag.
                                 </video>
                                 <?php } else { ?>
                                 <i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i>
                                 <?php echo $attachment['file_name']; ?>
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
                     ?>
                  <?php
                     $i++;
                     } ?>
               </div>
            </div>
            <div class="clearfix"></div>
            <?php if(($i - 1) > $show_more_link_tour_attachments){ ?>
            <div class="col-md-12" id="show-more-less-tour-attachments-col">
               <a href="#" class="tour-attachments-more" onclick="slideToggle('.tour_attachments_wrapper .tour-attachment-col-more', tour_attachments_toggle); return false;"><?php echo _l('show_more'); ?></a>
               <a href="#" class="tour-attachments-less hide" onclick="slideToggle('.tour_attachments_wrapper .tour-attachment-col-more', tour_attachments_toggle); return false;"><?php echo _l('show_less'); ?></a>
            </div>
            <?php } ?>
            <div class="col-md-12 text-center">
               <hr />
               <a href="<?php echo admin_url('tours/download_files/'.$tour->id); ?>" class="bold">
               <?php echo _l('download_all'); ?> (.zip)
               </a>
            </div>
         </div>
         <?php } ?>
         <hr />
         <a href="#" id="tourCommentSlide" onclick="slideToggle('.tours-comments'); return false;">
            <h4 class="mbot20 font-medium"><?php echo _l('tour_comments'); ?></h4>
         </a>
         <div class="tours-comments inline-block full-width simple-editor"<?php if(count($tour->comments) == 0){echo ' style="display:none"';} ?>>
            <?php echo form_open_multipart(admin_url('tours/add_tour_comment'),array('id'=>'tour-comment-form','class'=>'dropzone dropzone-manual','style'=>'min-height:auto;background-color:#fff;')); ?>
            <textarea name="comment" placeholder="<?php echo _l('tour_single_add_new_comment'); ?>" id="tour_comment" rows="3" class="form-control ays-ignore"></textarea>
            <div id="dropzoneTourComment" class="dropzoneDragArea dz-default dz-message hide tour-comment-dropzone">
               <span><?php echo _l('drop_files_here_to_upload'); ?></span>
            </div>
            <div class="dropzone-tour-comment-previews dropzone-previews"></div>
            <button type="button" class="btn btn-info mtop10 pull-right hide" id="addTourCommentBtn" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="add_tour_comment('<?php echo $tour->id; ?>');" data-comment-tour-id="<?php echo $tour->id; ?>">
            <?php echo _l('tour_single_add_new_comment'); ?>
            </button>
            <?php echo form_close(); ?>
            <div class="clearfix"></div>
            <?php if(count($tour->comments) > 0){echo '<hr />';} ?>
            <div id="tour-comments" class="mtop10">
               <?php
                  $comments = '';
                  $len = count($tour->comments);
                  $i = 0;
                  foreach ($tour->comments as $comment) {
                    $comments .= '<div id="comment_'.$comment['id'].'" data-commentid="' . $comment['id'] . '" data-tour-attachment-id="'.$comment['file_id'].'" class="tc-content tour-comment'.(strtotime($comment['dateadded']) >= strtotime('-16 hours') ? ' highlight-bg' : '').'">';
                    $comments .= '<a data-tour-comment-href-id="'.$comment['id'].'" href="'.admin_url('tours/view/'.$tour->id).'#comment_'.$comment['id'].'" class="tour-date-as-comment-id"><small><span class="text-has-action inline-block mbot5" data-toggle="tooltip" data-title="'._dt($comment['dateadded']).'">' . time_ago($comment['dateadded']) . '</span></small></a>';
                    if($comment['staffid'] != 0){
                     $comments .= '<a href="' . admin_url('profile/' . $comment['staffid']) . '" target="_blank">' . staff_profile_image($comment['staffid'], array(
                      'staff-profile-image-small',
                      'media-object img-circle pull-left mright10'
                   )) . '</a>';
                  } elseif($comment['contact_id'] != 0) {
                     $comments .= '<img src="'.contact_profile_image_url($comment['contact_id']).'" class="client-profile-image-small media-object img-circle pull-left mright10">';
                  }
                  if ($comment['staffid'] == get_staff_user_id() || is_admin()) {
                     $comment_added = strtotime($comment['dateadded']);
                     $minus_1_hour = strtotime('-1 hours');
                     if(get_option('client_staff_add_edit_delete_tour_comments_first_hour') == 0 || (get_option('client_staff_add_edit_delete_tour_comments_first_hour') == 1 && $comment_added >= $minus_1_hour) || is_admin()){
                       $comments .= '<span class="pull-right"><a href="#" onclick="remove_tour_comment(' . $comment['id'] . '); return false;"><i class="fa fa-times text-danger"></i></span></a>';
                       $comments .= '<span class="pull-right mright5"><a href="#" onclick="edit_tour_comment(' . $comment['id'] . '); return false;"><i class="fa-regular fa-pen-to-square"></i></span></a>';
                    }
                  }

                  $comments .= '<div class="media-body comment-wrapper">';
                  $comments .= '<div class="mleft40">';

                  if($comment['staffid'] != 0){
                   $comments .= '<a href="' . admin_url('profile/' . $comment['staffid']) . '" target="_blank">' . $comment['staff_full_name'] . '</a> <br />';
                  } elseif($comment['contact_id'] != 0) {
                   $comments .= '<span class="label label-info mtop5 mbot5 inline-block">'._l('is_customer_indicator').'</span><br /><a href="' . admin_url('booking/clients/client/'.get_user_id_by_contact_id($comment['contact_id']) .'?contactid='.$comment['contact_id'] ) . '" class="pull-left" target="_blank">' . get_contact_full_name($comment['contact_id']) . '</a> <br />';
                  }

                  $comments .= '<div data-edit-comment="'.$comment['id'].'" class="hide edit-tour-comment"><textarea rows="5" id="tour_comment_'.$comment['id'].'" class="ays-ignore form-control">'.str_replace('[tour_attachment]', '', $comment['content']).'</textarea>
                  <div class="clearfix mtop20"></div>
                  <button type="button" class="btn btn-info pull-right" onclick="save_edited_comment('.$comment['id'].','.$tour->id.')">'._l('submit').'</button>
                  <button type="button" class="btn btn-default pull-right mright5" onclick="cancel_edit_comment('.$comment['id'].')">'._l('cancel').'</button>
                  </div>';
                  if($comment['file_id'] != 0){
                  $comment['content'] = str_replace('[tour_attachment]','<div class="clearfix"></div>'.$attachments_data[$comment['file_id']],$comment['content']);
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
                   $comment['content'] .='<div class="text-center download-all">
                   <hr class="hr-10" />
                   <a href="'.admin_url('tours/download_files/'.$tour->id.'/'.$comment['id']).'" class="bold">'._l('download_all').' (.zip)
                   </a>
                   </div>';
                  }
                  $comments .= '<div class="comment-content mtop10">'.app_happy_text(check_for_links($comment['content'])) . '</div>';
                  $comments .= '</div>';
                  if ($i >= 0 && $i != $len - 1) {
                     $comments .= '<hr class="tour-info-separator" />';
                  }
                  $comments .= '</div>';
                  $comments .= '</div>';
                  $i++;
                  }
                  echo $comments;
                  ?>
            </div>
         </div>
      </div>
      <div class="col-md-4 tour-single-col-right">
         <div class="pull-right mbot10 tour-single-menu tour-menu-options">
            <div class="content-menu hide">
               <ul>
                  <?php if(has_permission('tours','','edit')) { ?>
                  <li>
                     <a href="#" onclick="edit_tour(<?php echo $tour->id; ?>); return false;">
                     <?php echo _l('tour_single_edit'); ?>
                     </a>
                  </li>
                  <?php } ?>
                  <?php if(has_permission('tours','','create')){ ?>
                  <?php
                     $copy_template = "";
                     if(count($tour->assignees) > 0){
                       $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_tour_assignees' id='copy_tour_assignees' checked><label for='copy_tour_assignees'>"._l('tour_single_assignees')."</label></div>";
                     }
                     if(count($tour->followers) > 0){
                     $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_tour_followers' id='copy_tour_followers' checked><label for='copy_tour_followers'>"._l('tour_single_followers')."</label></div>";
                     }
                     if(count($tour->checklist_items) > 0){
                     $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_tour_checklist_items' id='copy_tour_checklist_items' checked><label for='copy_tour_checklist_items'>"._l('tour_checklist_items')."</label></div>";
                     }
                     if(count($tour->attachments) > 0){
                     $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_tour_attachments' id='copy_tour_attachments'><label for='copy_tour_attachments'>"._l('tour_view_attachments')."</label></div>";
                     }

                     $copy_template .= "<p>"._l('tour_status')."</p>";
                     $tour_copy_statuses = hooks()->apply_filters('tour_copy_statuses', $tour_statuses);
                     foreach($tour_copy_statuses as $copy_status){
                     $copy_template .= "<div class='radio radio-primary'><input type='radio' value='".$copy_status['id']."' name='copy_tour_status' id='copy_tour_status_".$copy_status['id']."'".($copy_status['id'] == hooks()->apply_filters('copy_tour_default_status', 1) ? ' checked' : '')."><label for='copy_tour_status_".$copy_status['id']."'>".$copy_status['name']."</label></div>";
                     }

                     $copy_template .= "<div class='text-center'>";
                     $copy_template .= "<button type='button' data-tour-copy-from='".$tour->id."' class='btn btn-success copy_tour_action'>"._l('copy_tour_confirm')."</button>";
                     $copy_template .= "</div>";
                     ?>
                  <li> <a href="#" onclick="return false;" data-placement="bottom" data-toggle="popover" data-content="<?php echo htmlspecialchars($copy_template); ?>" data-html="true"><?php echo _l('tour_copy'); ?></span></a></li>
                  <?php } ?>
                  <?php if(has_permission('tours','','delete')){ ?>
                  <li>
                     <a href="<?php echo admin_url('tours/delete_tour/'.$tour->id); ?>" class="_delete tour-delete">
                     <?php echo _l('tour_single_delete'); ?>
                     </a>
                  </li>
                  <?php } ?>
               </ul>
            </div>
            <?php if(has_permission('tours','','delete') || has_permission('tours','','edit') || has_permission('tours','','create')){ ?>
            <a href="#" onclick="return false;" class="trigger manual-popover mright5">
            <i class="fa fa-circle-thin" aria-hidden="true"></i>
            <i class="fa fa-circle-thin" aria-hidden="true"></i>
            <i class="fa fa-circle-thin" aria-hidden="true"></i>
            </a>
            <?php } ?>
         </div>
         <h4 class="tour-info-heading"><?php echo _l('tour_info'); ?>
            <?php
               if($tour->recurring == 1){
                  echo '<span class="label label-info inline-block mleft5">'._l('recurring_tour').'</span>';
               }
               ?>
         </h4>
         <div class="clearfix"></div>
         <h5 class="no-mtop tour-info-created">
            <?php if(($tour->addedfrom != 0 && $tour->addedfrom != get_staff_user_id()) || $tour->is_added_from_contact == 1){ ?>
            <small class="text-dark"><?php echo _l('tour_created_by','<span class="text-dark">'.($tour->is_added_from_contact == 0 ? get_staff_full_name($tour->addedfrom) : get_contact_full_name($tour->addedfrom)).'</span>'); ?> <i class="fa fa-clock-o" data-toggle="tooltip" data-title="<?php echo _l('tour_created_at',_dt($tour->dateadded)); ?>"></i></small>
            <br />
            <?php } else { ?>
            <small class="text-dark"><?php echo _l('tour_created_at','<span class="text-dark">'._dt($tour->dateadded).'</span>'); ?></small>
            <?php } ?>
         </h5>
         <hr class="tour-info-separator" />
         <div class="tour-info tour-status tour-info-status">
            <h5>
               <i class="fa fa-<?php if($tour->status == Tours_model::STATUS_COMPLETE){echo 'star';} else if($tour->status == 1){echo 'star-o';} else {echo 'star-half-o';} ?> pull-left tour-info-icon fa-fw fa-lg"></i><?php echo _l('tour_status'); ?>:
               <?php if($tour->current_user_is_assigned || $tour->current_user_is_creator || has_permission('tours','','edit')) { ?>
               <span class="tour-single-menu tour-menu-status">
                  <span class="trigger pointer manual-popover text-has-action">
                  <?php echo format_tour_status($tour->status,true); ?>
                  </span>
                  <span class="content-menu hide">
                     <ul>
                        <?php
                           $tour_single_mark_as_statuses = hooks()->apply_filters('tour_single_mark_as_statuses', $tour_statuses);
                           foreach($tour_single_mark_as_statuses as $status){ ?>
                        <?php if($tour->status != $status['id']){ ?>
                        <li>
                           <a href="#" onclick="tour_mark_as(<?php echo $status['id']; ?>,<?php echo $tour->id; ?>); return false;">
                           <?php echo _l('tour_mark_as',$status['name']); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <?php } ?>
                     </ul>
                  </span>
               </span>
               <?php } else { ?>
               <?php echo format_tour_status($tour->status,true); ?>
               <?php } ?>
            </h5>
         </div>
         <?php if($tour->status == Tours_model::STATUS_COMPLETE){ ?>
         <div class="tour-info tour-info-finished">
            <h5><i class="fa tour-info-icon fa-fw fa-lg pull-left fa-check"></i>
               <?php echo _l('tour_single_finished'); ?>: <span data-toggle="tooltip" data-title="<?php echo _dt($tour->datefinished); ?>" data-placement="bottom" class="text-has-action"><?php echo time_ago($tour->datefinished); ?></span>
            </h5>
         </div>
         <?php } ?>
         <div class="tour-info tour-single-inline-wrap tour-info-start-date">
            <h5><i class="fa tour-info-icon fa-fw fa-lg fa-calendar-plus-o pull-left fa-margin"></i>
               <?php echo _l('tour_single_start_date'); ?>:
               <?php if(has_permission('tours','','edit') && $tour->status !=5) { ?>
               <input name="startdate" tabindex="-1" value="<?php echo _d($tour->startdate); ?>" id="tour-single-startdate" class="tour-info-inline-input-edit datepicker pointer tour-single-inline-field">
               <?php } else { ?>
               <?php echo _d($tour->startdate); ?>
               <?php } ?>
            </h5>
         </div>
         <div class="tour-info tour-info-due-date tour-single-inline-wrap<?php if(!$tour->duedate && !has_permission('edit','','tours')){echo ' hide';} ?>"<?php if(!$tour->duedate){ echo ' style="opacity:0.5;"';} ?>>
            <h5>
               <i class="fa fa-calendar-check-o tour-info-icon fa-fw fa-lg pull-left"></i>
               <?php echo _l('tour_single_due_date'); ?>:
               <?php if(has_permission('tours','','edit') && $tour->status !=5) { ?>
               <input
               name="duedate"
               tabindex="-1"
               value="<?php echo _d($tour->duedate); ?>"
               id="tour-single-duedate"
               class="tour-info-inline-input-edit datepicker pointer tour-single-inline-field"
               autocomplete="off"
               <?php if($booking_deadline){echo ' data-date-end-date="'.$booking_deadline.'"';} ?>>
               <?php } else { ?>
               <?php echo _d($tour->duedate); ?>
               <?php } ?>
            </h5>
         </div>
         <div class="tour-info tour-info-priority">
            <h5>
               <i class="fa tour-info-icon fa-fw fa-lg pull-left fa-bolt"></i>
               <?php echo _l('tour_single_priority'); ?>:
               <?php if(has_permission('tours','','edit') && $tour->status != Tours_model::STATUS_COMPLETE) { ?>
               <span class="tour-single-menu tour-menu-priority">
                  <span class="trigger pointer manual-popover text-has-action" style="color:<?php echo tour_priority_color($tour->priority); ?>;">
                  <?php echo tour_priority($tour->priority); ?>
                  </span>
                  <span class="content-menu hide">
                     <ul>
                        <?php
                           foreach(get_tours_priorities() as $priority){ ?>
                        <?php if($tour->priority != $priority['id']){ ?>
                        <li>
                           <a href="#" onclick="tour_change_priority(<?php echo $priority['id']; ?>,<?php echo $tour->id; ?>); return false;">
                           <?php echo $priority['name']; ?>
                           </a>
                        </li>
                        <?php } ?>
                        <?php } ?>
                     </ul>
                  </span>
               </span>
               <?php } else { ?>
               <span style="color:<?php echo tour_priority_color($tour->priority); ?>;">
               <?php echo tour_priority($tour->priority); ?>
               </span>
               <?php } ?>
            </h5>
         </div>
         <?php if($tour->current_user_is_creator || has_permission('tours','','edit')){ ?>
         <div class="tour-info tour-info-hourly-rate">
            <h5><i class="fa tour-info-icon fa-fw fa-lg pull-left fa-clock-o"></i>
               <?php echo _l('tour_hourly_rate'); ?>: <?php if($tour->rel_type == 'booking' && $tour->booking_data->billing_type == 2){
                  echo app_format_number($tour->booking_data->booking_rate_per_hour);
                  } else {
                  echo app_format_number($tour->hourly_rate);
                  }
                  ?>
            </h5>
         </div>
         <div class="tour-info tour-info-billable">
            <h5><i class="fa tour-info-icon fa-fw fa-lg pull-left fa fa-credit-card"></i>
               <?php echo _l('tour_billable'); ?>: <?php echo ($tour->billable == 1 ? _l('tour_billable_yes') : _l('tour_billable_no')) ?>
               <?php if($tour->billable == 1){ ?>
               <b>(<?php echo ($tour->billed == 1 ? _l('tour_billed_yes') : _l('tour_billed_no')) ?>)</b>
               <?php } ?>
               <?php if($tour->rel_type == 'booking' && $tour->booking_data->billing_type == 1){echo '<small>('._l('booking'). ' ' . _l('booking_billing_type_fixed_cost').')</small>';} ?>
            </h5>
         </div>
         <?php if($tour->billable == 1
            && $tour->billed == 0
            && ($tour->rel_type != 'booking' || ($tour->rel_type == 'booking' && $tour->booking_data->billing_type != 1))
            && staff_can('create', 'invoices')) { ?>
         <div class="tour-info tour-billable-amount">
            <h5><i class="fa tour-info-icon fa-fw fa-lg pull-left fa fa-file-text-o"></i>
               <?php echo _l('billable_amount'); ?>:
               <span class="bold">
               <?php echo $this->tours_model->get_billable_amount($tour->id); ?>
               </span>
            </h5>
         </div>
         <?php } ?>
         <?php } ?>
         <?php if($tour->current_user_is_assigned || total_rows(db_prefix().'tourstimers',array('tour_id'=>$tour->id,'staff_id'=>get_staff_user_id())) > 0){ ?>
         <div class="tour-info tour-info-user-logged-time">
            <h5>
               <i class="fa fa-asterisk tour-info-icon fa-fw fa-lg" aria-hidden="true"></i><?php echo _l('tour_user_logged_time'); ?>
               <?php echo seconds_to_time_format($this->tours_model->calc_tour_total_time($tour->id,' AND staff_id='.get_staff_user_id())); ?>
            </h5>
         </div>
         <?php } ?>
         <?php if(has_permission('tours','','create')){ ?>
         <div class="tour-info tour-info-total-logged-time">
            <h5>
               <i class="fa tour-info-icon fa-fw fa-lg fa-clock-o"></i><?php echo _l('tour_total_logged_time'); ?>
               <span class="text-success">
               <?php echo seconds_to_time_format($this->tours_model->calc_tour_total_time($tour->id)); ?>
               </span>
            </h5>
         </div>
         <?php } ?>
         <?php $custom_fields = get_custom_fields('tours');
            foreach($custom_fields as $field){ ?>
         <?php $value = get_custom_field_value($tour->id,$field['id'],'tours');
            if($value == ''){continue;}?>
         <div class="tour-info">
            <h5 class="tour-info-custom-field tour-info-custom-field-<?php echo $field['id']; ?>">
               <i class="fa tour-info-icon fa-fw fa-lg fa-square-o"></i>
               <?php echo $field['name']; ?>: <?php echo $value; ?>
            </h5>
         </div>
         <?php } ?>
         <?php if(has_permission('tours','','create') || has_permission('tours','','edit')){ ?>
         <div class="mtop5 clearfix"></div>
         <div id="inputTagsWrapper" class="tourSingleTours tour-info-tags-edit">
            <input type="text" class="tagsinput" id="tourTags" data-tourid="<?php echo $tour->id; ?>" value="<?php echo prep_tags_input(get_tags_in($tour->id,'tour')); ?>" data-role="tagsinput">
         </div>
         <div class="clearfix"></div>
         <?php } else { ?>
         <div class="mtop5 clearfix"></div>
         <?php echo render_tags(get_tags_in($tour->id,'tour')); ?>
         <div class="clearfix"></div>
         <?php } ?>
         <hr class="tour-info-separator" />
         <div class="clearfix"></div>
         <?php if($tour->current_user_is_assigned){
            foreach($tour->assignees as $assignee){
              if($assignee['assigneeid'] == get_staff_user_id() && get_staff_user_id() != $assignee['assigned_from'] && $assignee['assigned_from'] != 0 || $assignee['is_assigned_from_contact'] == 1){
               if($assignee['is_assigned_from_contact'] == 0){
                 echo '<p class="text-muted tour-assigned-from">'._l('tour_assigned_from','<a href="'.admin_url('profile/'.$assignee['assigned_from']).'" target="_blank">'.get_staff_full_name($assignee['assigned_from'])).'</a></p>';
              } else {
               echo '<p class="text-muted tour-assigned-from tour-assigned-from-contact">'._l('tour_assigned_from',get_contact_full_name($assignee['assigned_from'])).'<br /><span class="label inline-block mtop5 label-info">'._l('is_customer_indicator').'</span></p>';
            }
            break;
            }
            }
            } ?>
         <h4 class="tour-info-heading font-normal font-medium-xs">
            <i class="fa fa-bell-o" aria-hidden="true"></i>
            <?php echo _l('reminders'); ?>
         </h4>
         <a href="#" onclick="new_tour_reminder(<?php echo $tour->id; ?>); return false;">
         <?php echo _l('create_reminder'); ?>
         </a>
         <?php if(count($reminders) == 0) { ?>
         <div class="display-block text-muted mtop10">
            <?php echo _l('no_reminders_for_this_tour'); ?>
         </div>
         <?php } else { ?>
         <ul class="mtop10">
            <?php foreach($reminders as $rKey => $reminder) {
               ?>
            <li class="<?php if($reminder['isnotified'] == '1'){echo 'text-throught';} ?>" data-id="<?php echo $reminder['id']; ?>">
               <div class="mbot15">
                  <div>
                     <p class="bold">
                        <?php echo _l('reminder_for', [
                           get_staff_full_name($reminder['staff']),
                           _dt($reminder['date'])
                        ]); ?>
                        <?php if ($reminder['creator'] == get_staff_user_id() || is_admin()) { ?>
                        <?php if($reminder['isnotified'] == 0){ ?>
                        <a href="#" class="  text-muted" onclick="edit_reminder(<?php echo $reminder['id']; ?>, this); return false;">
                        <i class="fa fa-edit"></i>
                        </a>
                        <?php } ?>
                        <a href="<?php echo admin_url('tours/delete_reminder/' . $tour->id . '/' . $reminder['id']); ?>" class="text-danger delete-reminder"><i class="fa fa-remove"></i></a>
                        <?php } ?>
                     </p>
                     <?php
                        if(!empty($reminder['description'])) {
                           echo $reminder['description'];
                        } else {
                           echo '<p class="text-muted no-mbot">'._l('no_description_provided').'</p>';
                        }
                        ?>
                  </div>
                  <?php if(count($reminders) -1 != $rKey) { ?>
                  <hr class="hr-10" />
                  <?php } ?>
               </div>
            </li>
            <?php } ?>
         </ul>
         <?php } ?>
         <div class="clearfix"></div>
         <div id="newTourReminderToggle" class="mtop15" style="display:none;">
            <?php echo form_open('', array('id'=>'form-reminder-tour')); ?>
            <?php $this->load->view('admin/includes/reminder_fields',['members'=>$staff_reminders, 'id'=>$tour->id, 'name'=>'tour']); ?>
            <button class="btn btn-info btn-xs pull-right" type="submit" id="tourReminderFormSubmit">
            <?php echo _l('create_reminder'); ?>
            </button>
            <div class="clearfix"></div>
            <?php echo form_close(); ?>
         </div>
         <hr class="tour-info-separator" />
         <div class="clearfix"></div>
         <h4 class="tour-info-heading font-normal font-medium-xs"><i class="fa fa-user-o" aria-hidden="true"></i> <?php echo _l('tour_single_assignees'); ?></h4>
         <?php if(has_permission('tours','','edit') || has_permission('tours','','create')){ ?>
         <div class="simple-bootstrap-select">
            <select data-width="100%" <?php if($tour->rel_type=='booking'){ ?> data-live-search-placeholder="<?php echo _l('search_booking_members'); ?>" <?php } ?> data-tour-id="<?php echo $tour->id; ?>" id="add_tour_assignees" class="text-muted tour-action-select selectpicker" name="select-assignees" data-live-search="true" title='<?php echo _l('tour_single_assignees_select_title'); ?>' data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
            <?php
               $options = '';
               foreach ($staff as $assignee) {
                 if (!in_array($assignee['staffid'],$tour->assignees_ids)) {
                   if ($tour->rel_type == 'booking'
                     && total_rows(db_prefix().'booking_members', array('booking_id' => $tour->rel_id,'staff_id' => $assignee['staffid'])) == 0) {
                    continue;
               }
               $options .= '<option value="' . $assignee['staffid'] . '">' . $assignee['full_name'] . '</option>';
               }
               }
               echo $options;
               ?>
            </select>
         </div>
         <?php } ?>
         <div class="tour_users_wrapper">
            <?php
               $_assignees = '';
               foreach ($tour->assignees as $assignee) {
                $_remove_assigne = '';
                if(has_permission('tours','','edit') || has_permission('tours','','create')){
                  $_remove_assigne = ' <a href="#" class="remove-tour-user text-danger" onclick="remove_assignee(' . $assignee['id'] . ',' . $tour->id . '); return false;"><i class="fa fa-remove"></i></a>';
               }
               $_assignees .= '
               <div class="tour-user"  data-toggle="tooltip" data-title="'.html_escape($assignee['full_name']).'">
               <a href="' . admin_url('profile/' . $assignee['assigneeid']) . '" target="_blank">' . staff_profile_image($assignee['assigneeid'], array(
                'staff-profile-image-small'
               )) .'</a> ' . $_remove_assigne . '</span>
               </div>';
               }
               if ($_assignees == '') {
               $_assignees = '<div class="text-danger display-block">'._l('tour_no_assignees').'</div>';
               }
               echo $_assignees;
               ?>
         </div>
         <hr class="tour-info-separator" />
         <div class="clearfix"></div>
         <h4 class="tour-info-heading font-normal font-medium-xs">
            <i class="fa fa-user-o" aria-hidden="true"></i>
            <?php echo _l('tour_single_followers'); ?>
         </h4>
         <?php if(has_permission('tours','','edit') || has_permission('tours','','create')){ ?>
         <div class="simple-bootstrap-select">
            <select data-width="100%" data-tour-id="<?php echo $tour->id; ?>" class="text-muted selectpicker tour-action-select" name="select-followers" data-live-search="true" title='<?php echo _l('tour_single_followers_select_title'); ?>' data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
            <?php
               $options = '';
               foreach ($staff as $follower) {
                if (!in_array($follower['staffid'],$tour->followers_ids)) {
                  $options .= '<option value="' . $follower['staffid'] . '">' . $follower['full_name'] . '</option>';
               }
               }
               echo $options;
               ?>
            </select>
         </div>
         <?php } ?>
         <div class="tour_users_wrapper">
            <?php
               $_followers        = '';
               foreach ($tour->followers as $follower) {
                 $_remove_follower = '';
                 if(has_permission('tours','','edit') || has_permission('tours','','create')){
                   $_remove_follower = ' <a href="#" class="remove-tour-user text-danger" onclick="remove_follower(' . $follower['id'] . ',' . $tour->id . '); return false;"><i class="fa fa-remove"></i></a>';
                }
                $_followers .= '
                <span class="tour-user" data-toggle="tooltip" data-title="'.html_escape($follower['full_name']).'">
                <a href="' . admin_url('profile/' . $follower['followerid']) . '" target="_blank">' . staff_profile_image($follower['followerid'], array(
                 'staff-profile-image-small'
               )) . '</a> ' . $_remove_follower . '</span>
                </span>';
               }
               if ($_followers == '') {
               $_followers = '<div class="display-block text-muted mbot15">'._l('tour_no_followers').'</div>';
               }
               echo $_followers;
               ?>
         </div>
         <hr class="tour-info-separator" />
         <?php echo form_open_multipart('admin/tours/upload_file',array('id'=>'tour-attachment','class'=>'dropzone')); ?>
         <?php echo form_close(); ?>
         <div class="mtop10 text-right">
            <button class="gpicker mbot5">
            <i class="fa fa-google" aria-hidden="true"></i>
            <?php echo _l('choose_from_google_drive'); ?>
            </button>
            <div id="dropbox-chooser-tour"></div>
         </div>
      </div>
   </div>
</div>
<script>
   if(typeof(commonTourPopoverMenuOptions) == 'undefined') {
      var commonTourPopoverMenuOptions = {
         html: true,
         placement: 'bottom',
         trigger: 'click',
         template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"></div></div></div>',
      };
   }

   // Clear memory leak
   if(typeof(tourPopoverMenus) == 'undefined'){
      var tourPopoverMenus = [{
            selector: '.tour-menu-options',
            title: "<?php echo _l('actions'); ?>",
         },
         {
            selector: '.tour-menu-status',
            title: "<?php echo _l('ticket_single_change_status'); ?>",
         },
         {
            selector: '.tour-menu-priority',
            title: "<?php echo _l('tour_single_priority'); ?>",
         },
         {
            selector: '.tour-menu-milestones',
            title: "<?php echo _l('tour_milestone'); ?>",
         },
      ];
   }

   for (var i = 0; i < tourPopoverMenus.length; i++) {
      $(tourPopoverMenus[i].selector + ' .trigger').popover($.extend({}, commonTourPopoverMenuOptions, {
         title: tourPopoverMenus[i].title,
         content: $('body').find(tourPopoverMenus[i].selector + ' .content-menu').html()
      }));
   }

   if (typeof (Dropbox) != 'undefined') {
      document.getElementById("dropbox-chooser-tour").appendChild(Dropbox.createChooseButton({
         success: function (files) {
            tourExternalFileUpload(files,'dropbox',<?php echo $tour->id; ?>);
         },
         linkType: "preview",
         extensions: app.options.allowed_files.split(','),
      }));
   }

   init_selectpicker();
   init_datepicker();
   init_lightbox();

   tinyMCE.remove('#tour_view_description');

   if (typeof (tourAttachmentDropzone) != 'undefined') {
      tourAttachmentDropzone.destroy();
      tourAttachmentDropzone = null;
   }

   tourAttachmentDropzone = new Dropzone("#tour-attachment", appCreateDropzoneOptions({
      uploadMultiple: true,
      parallelUploads: 20,
      maxFiles: 20,
      paramName: 'file',
      sending: function (file, xhr, formData) {
         formData.append("tourid", '<?php echo $tour->id; ?>');
      },
      success: function (files, response) {
         response = JSON.parse(response);
         if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
            _tour_append_html(response.tourHtml);
         }
      }
   }));

   $('#tour-modal').find('.gpicker').googleDrivePicker({ onPick: function(pickData) {
      tourExternalFileUpload(pickData, 'gdrive', <?php echo $tour->id; ?>);
   }});

</script>
