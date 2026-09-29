<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('_attachment_sale_id',$reminder->id); ?>
<?php echo form_hidden('_attachment_sale_type','proposal'); ?>
<div class="panel_s">
   <div class="panel-body">
      <div class="horizontal-scrollable-tabs preview-tabs-top">
         <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
         <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
         <div class="horizontal-tabs">
            <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
               <li role="presentation" class="active">
                  <a href="#tab_proposal" aria-controls="tab_proposal" role="tab" data-toggle="tab">
                     <?php echo _l('reminder'); ?>
                  </a>
               </li>
               <?php if(isset($reminder)){ ?>
                  <li role="presentation">
                     <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab" >
                        <?php echo _l('proposal_view_activity_tooltip'); ?>
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
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="row">
            <div class="col-md-12">
               <div class="tab-content">
                  <div role="tabpanel" class="tab-pane active" id="tab_proposal">
                     <div class="row ">
                        <div class="col-sm-12">
                           <address>
                             <?php echo $reminder->description; ?>
                          </address>
                          <table class="table text-left items">
                           <thead >
                              <tr><th colspan="2">Contact Information</th></tr>
                           </thead>
                           <tbody>
                             <tr>
                              <td><span class="bold">Customer</span></td>
                              <td class="total"> <a href="<?php echo admin_url()?>clients/client/<?php echo $reminder->customer  ?>" target="_blank"><?php echo $reminder->company; ?> </a> </td>
                           </tr>
                           <tr id="subtotal">
                              <td><span class="bold">Contact</span></td>
                              <td class="subtotal"><a href="<?php echo admin_url()?>clients/client/<?php echo $reminder->contact?>?group=contacts&contactid=<?php echo $reminder->contact ?>" target="_blank"> <?php echo get_contact_full_name($reminder->contact); ?></td>
                              </tr>
                              <tr class="tax-area">
                                 <td class="bold">Email</td><td><a href="mailto: <?php echo $reminder->email; ?>"><?php echo $reminder->email; ?></a></td></tr>
                                 <tr class="tax-area"><td class="bold">Telephone</td><td><a href="tel:<?php echo $reminder->phonenumber; ?>"><?php echo $reminder->phonenumber; ?></a></td></tr>
                                 <tr class="tax-area">
                                    <?php if($reminder->rel_type=='invoice'){ ?>
                                      <td class="bold"><?php echo $reminder->rel_type; ?></td>
                                      <td>
                                       <a href="<?php echo admin_url()?>invoices/invoice/<?php echo $reminder->rel_id ?>" target="_blank"> <?php echo format_invoice_number($reminder->rel_id); ?>
                                    </a>
                                 </td>
                              <?php } ?>
                              <?php if($reminder->rel_type=='quotes'){ ?>
                                <td class="bold">Quote</td>
                                <td>
                                 <a href="<?php echo admin_url()?>quotes/quote/<?php echo $reminder->rel_id ?>" target="_blank"> <?php echo format_proposal_number($reminder->rel_id); ?>
                              </a>
                           </td>
                        <?php } ?>
                        <?php if($reminder->rel_type=='estimate'){ ?>
                          <td class="bold">Order</td>
                          <td>
                           <a href="<?php echo admin_url()?>orders/order/<?php echo $reminder->rel_id ?>" target="_blank"> <?php echo format_estimate_number($reminder->rel_id); ?>
                        </a>
                     </td>
                  <?php } ?>
                  <?php if($reminder->rel_type=='credit_note'){ ?>
                    <td class="bold">Credit Notes</td>
                    <td>
                     <a href="<?php echo admin_url()?>credit_notes/credit_note/<?php echo $reminder->rel_id ?>" target="_blank"> <?php echo format_credit_note_number($reminder->rel_id); ?>
                  </a>
               </td>
            <?php } ?>
            <?php if($reminder->rel_type=='job'){ ?>
              <td class="bold">Job</td>
              <td>
               <a href="<?php echo admin_url()?>jobs/job/<?php echo $reminder->rel_id ?>" target="_blank"> <?php echo format_job_number($reminder->rel_id); ?>
            </a>
         </td>
      <?php } ?>
   </tr>
</tbody>
</table>
  <hr class="hr-panel-heading" />
 </div>
</div>
<div class="row">
   <?php echo form_open(admin_url().'reminder/reminder_new/',array('id'=>'reminder-form','class'=>'_transaction_form_reminder reminder-form new_items_table','id'=>'reminder-form')); ?>               
   <div class="col-md-12">
      <div class="panel_s">
         <div class="panel-body">
            <div class="row">
               <div class="col-md-6 border-right">
                 <?php
                 $value = (isset($reminderData) ? _d($reminderData->date) : '');
                 if($reminderData->is_complete=='0')
                  {?>
                     <?php echo render_datetime_input('date','set_reminder_date',$value,array('data-date-min-date'=>_d(date('Y-m-d')),'disabled'=>true) ); 
                  }else{
                     echo render_datetime_input('date','set_reminder_date',$value,array('data-date-min-date'=>_d(date('Y-m-d'))) ); 
                  }
                  ?>
               </div>
               <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12">
                     <?php 
                     $i = 0;
                     $selected = '';
                     foreach($staff as $member){
                       if(isset($reminderData)){
                        if($reminderData->assigned_to == $member['staffid']) {
                         $selected = $member['staffid'];
                      }
                   }
                   $i++;
                }
                if($reminderData->is_complete=='0'){
                  echo render_select('assigned_to',$staff,array('staffid',array('firstname','lastname')),'reminder_assigned',$selected,array('disabled'=>true));
               }else{
                  echo render_select('assigned_to',$staff,array('staffid',array('firstname','lastname')),'reminder_assigned',$selected);
               }
               ?>
            </div>
         </div>
      </div>
      <div class="col-md-12">
       <?php if(isset($reminderData)){
        $val=$reminderData->description;
     }else{ $val='';} ?>
     <?php
     if($reminderData->is_complete=='0'){
       $btnSts='disabled';
       echo render_textarea('description','reminder_description',$val,array('disabled'=>true)); 
    }else{
      $btnSts='';
      echo render_textarea('description','reminder_description',$val); 
   }
   ?>
</div>
         <input type="hidden" name="id" value="<?php echo (isset($reminderData))?$reminderData->id:''; ?>">
            <div class="col-sm-12 float-right ">
                <button class="btn btn-info  float-right mr-5" <?php echo $btnSts; ?> type="submit" style="float: right;margin-left: 20px">
                 <?php echo _l('save_and_exit'); ?>
               </button>
         <?php if($reminderData->is_complete=='1'){  ?>
          <a href="<?php echo admin_url()?>reminder/complete_reminder/<?php echo $reminderData->id; ?>"> 
            <button class="btn btn-success  float-right" type="button" style="float: right;">
            <?php echo _l('complete'); ?>
            </button>
         </a>
          <?php }
          else{ 
            ?>
               <a href="<?php echo admin_url()?>reminder/reopen_reminder/<?php echo $reminderData->id; ?>"> 
                  <button class="btn btn-success  float-right" type="button" style="float: right;">
                   <?php echo _l('reopen_reminder');?>
                 </button>
               </a>
         <?php } ?>
         </div>
       </div>
      </div>
    </div>
</div>
<?php echo form_close(); ?>
</div>
       <hr class="hr-panel-heading" />
        <div class="clearfix"></div>
   </div>
<div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
   <?php
   $this->load->view('admin/includes/emails_tracking',array(
    'tracked_emails'=>
    get_tracked_emails($reminder->rel_id, $reminder->rel_type))
);
?>
</div>
<div role="tabpanel" class="tab-pane" id="tab_tasks">
   <?php init_relation_tasks_table(array( 'data-new-rel-id'=>$reminder->id,'data-new-rel-type'=>'proposal')); ?>
</div>
<div role="tabpanel" class="tab-pane" id="tab_reminders">
   <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-proposal-<?php echo $reminder->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('proposal_set_reminder_title'); ?></a>
   <hr />
   <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified')), 'reminders'); ?>
   <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$reminder->id,'name'=>'proposal','members'=>$members,'reminder_title'=>_l('proposal_set_reminder_title'))); ?>
</div>
<div role="tabpanel" class="tab-pane ptop10" id="tab_views">
   <?php
   $views_activity = get_views_tracking('reminder',$reminder->id);
   if(count($views_activity) === 0) {
      echo '<h4 class="no-margin">'._l('not_viewed_yet',_l('proposal_lowercase')).'</h4>';
   }
   foreach($views_activity as $activityy){ ?>
      <p class="text-success no-margin">
         <?php echo _l('view_date') . ': ' . _dt($activityy['date']); ?>
      </p>
      <p class="text-muted">
         <?php echo _l('view_ip') . ': ' . $activityy['view_ip']; ?>
      </p>
      <hr />
   <?php } ?>
</div>
<div role="tabpanel" class="tab-pane" id="tab_activity">
   <div class="row">
      <div class="col-md-12">
       <div class="row proposal-comments mtop15">
         <div class="col-md-12">
            <!-- <div id="proposal-comments"></div> -->
            <div class="clearfix"></div>
            <textarea name="content" id="comment" rows="4" class="form-control mtop15 reminder-comment"></textarea>
            <button type="button" class="btn btn-info mtop10 pull-right" onclick="add_reminder_comment();"><?php echo _l('reminder_add_comment'); ?>
            </button>
         </div>
      </div>
      <div class="activity-feed">
                              <?php foreach($activity as $activity)
                              {
                                 $_custom_data = false;
                                 ?>
                                 <div class="feed-item" data-sale-activity-id="<?php echo $activity['id']; ?>">
                                   <div class="date">
                                    <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($activity['date']); ?>">
                                       <?php echo time_ago($activity['date']); ?>
                                    </span>
                                 </div> 
                                 <div class="text">
                                    <?php if(!empty($activity['staffid']) && is_numeric($activity['staffid']) && $activity['staffid'] != 0){ ?>
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
                                 $_formatted_activity .= '' .$_custom_data;
                              }
                              if(!empty($activity['full_name'])){
                                 $_formatted_activity = $activity['full_name'] . ' - ' . $_formatted_activity;
                              }
                              echo $_formatted_activity;
                              if(is_admin()){
                                 echo '<a href="#" class="pull-right text-danger" onclick="delete_reminder_activity('.$activity['id'].'); return false;"><i class="fa fa-remove"></i></a>';
                              }
                              ?>
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
</div>
<div id="modal-wrapper"></div>
<script>
   "use strict";
   init_items_sortable(true);
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();
   var reminder_id = '<?php echo $reminder->id; ?>';
   init_proposal_editor();
   $('body').on('change','#template_id', function() {
    if($(this).val() != ''){
      $.get(admin_url + 'quotes/get_template_data/' + $(this).val(), function(response) {
         if(typeof response.message != 'undefined' && response.message != '') {
            tinymce.get("email_template_custom").setContent(response.message);
         }
      }, 'json');
   }
});
   function add_reminder_comment() {
     var comment = $('#comment').val();
     if (comment == '') {
       return;
    }
    var data = {};
    data.content = comment;
    data.reminder_id = reminder_id;
    $('body').append('<div class="dt-loader"></div>');
    $.post(admin_url + 'reminder/add_reminder_comment', data).done(function (response) {
       response = JSON.parse(response);
       $('body').find('.dt-loader').remove();
       if (response.success == true) {
         $('.reminder-comment').val('');
         get_reminder_activity(reminder_id);
      }
   });
    
 }
 function get_reminder_activity() {
  if (typeof (reminder_id) == 'undefined') {
    return;
 }
 requestGet('reminder/get_reminder_activity/' + reminder_id).done(function (response) {
    $('body').find('.activity-feed').html(response);
 });
}
function delete_reminder_activity(id) {
  if (confirm_delete()) {
    requestGet('reminder/delete_reminder_activity/' + id).done(function () {
      $("body").find('[data-sale-activity-id="' + id + '"]').remove();
   });
 }
}
function validate_reminder_form(selector) {
   selector = typeof (selector) == 'undefined' ? '#reminder-form' : selector;
   appValidateForm($(selector), {
      date: 'required',
      customer: 'required',
      description: 'required',
      contact: 'required',
      assigned_to: 'required',
      rel_type: 'required',
   });
}
$(function(){
  validate_reminder_form();
  $('#small-table').addClass('hide');
});
var _rel_id = $('#rel_id'),
_rel_type = $('#rel_type'),
_rel_id_wrapper = $('#rel_id_wrapper'),
data = {};
$('body').on('change','#customer', function() {
 if($(this).val() != ''){
   $.get(admin_url + 'reminder/get_contact_data_values/' + $(this).val() + '/customer', function(response) {
     $('.proposal_to_wrap').html(response.field_to);
     $('#contact').selectpicker('refresh');
  }, 'json');
}
});
_rel_type.on('change', function() {
 var clonedSelect = _rel_id.html('').clone();
 _rel_id.selectpicker('destroy').remove();
 _rel_id = clonedSelect;
 $('#rel_id_select').append(clonedSelect);
 $('.rel_id_label').html(_rel_type.find('option:selected').text());
 reminder_rel_select();
 if($(this).val() != ''){
   _rel_id_wrapper.removeClass('hide');
} else {
   _rel_id_wrapper.addClass('hide');
}
});
function reminder_rel_select(){
 data = {};
 var serverData = {};
 serverData.rel_id = $('#rel_id').val();
 data.type = $('#rel_type').val();
 init_ajax_search($('#rel_type').val(),$('#rel_id'),serverData);
}
</script>
