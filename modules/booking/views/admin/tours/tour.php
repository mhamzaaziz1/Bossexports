<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open_multipart(admin_url('tours/tour/'.$id),array('id'=>'tour-form')); ?>
<div class="modal fade<?php if(isset($tour)){echo ' edit';} ?>" id="_tour_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"<?php if($this->input->get('opened_from_lead_id')){echo 'data-lead-id='.$this->input->get('opened_from_lead_id'); } ?>>
<div class="modal-dialog" role="document">
   <div class="modal-content">
      <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
         <h4 class="modal-title" id="myModalLabel">
            <?php echo $title; ?>
         </h4>
      </div>
      <div class="modal-body">
         <div class="row">
            <div class="col-md-12">
               <?php
                  $rel_type = '';
                  $rel_id = '';
                  if(isset($tour) || ($this->input->get('rel_id') && $this->input->get('rel_type'))){
                      $rel_id = isset($tour) ? $tour->rel_id : $this->input->get('rel_id');
                      $rel_type = isset($tour) ? $tour->rel_type : $this->input->get('rel_type');
                   }
                   if(isset($tour) && $tour->billed == 1){
                     echo '<div class="alert alert-success text-center no-margin">'._l('tour_is_billed','<a href="'.admin_url('invoices/list_invoices/'.$tour->invoice_id).'" target="_blank">'.format_invoice_number($tour->invoice_id)). '</a></div><br />';
                   }
                  ?>
               <?php if(isset($tour)){ ?>
               <div class="pull-right mbot10 tour-single-menu tour-menu-options">
                  <div class="content-menu hide">
                     <ul>
                        <?php if(has_permission('tours','','create')){ ?>
                        <?php
                           $copy_template = "";
                           if(total_rows(db_prefix().'tour_assigned',array('tourid'=>$tour->id)) > 0){
                             $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_tour_assignees' id='copy_tour_assignees' checked><label for='copy_tour_assignees'>"._l('tour_single_assignees')."</label></div>";
                           }
                           if(total_rows(db_prefix().'tour_followers',array('tourid'=>$tour->id)) > 0){
                             $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_tour_followers' id='copy_tour_followers' checked><label for='copy_tour_followers'>"._l('tour_single_followers')."</label></div>";
                           }
                           if(total_rows(db_prefix().'tour_checklist_items',array('tourid'=>$tour->id)) > 0){
                            $copy_template .= "<div class='checkbox checkbox-primary'><input type='checkbox' name='copy_tour_checklist_items' id='copy_tour_checklist_items' checked><label for='copy_tour_checklist_items'>"._l('tour_checklist_items')."</label></div>";
                           }
                           if(total_rows(db_prefix().'files',array('rel_id'=>$tour->id,'rel_type'=>'tour')) > 0){
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
                        <li> <a href="#" onclick="return false;" data-placement="bottom" data-toggle="popover" data-content="<?php echo htmlspecialchars($copy_template); ?>" data-html="true"><?php echo _l('tour_copy'); ?></span></a>
                        </li>
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
                  <?php if(has_permission('tours','','delete') || has_permission('tours','','create')){ ?>
                  <a href="#" onclick="return false;" class="trigger manual-popover mright5">
                  <i class="fa fa-circle-thin" aria-hidden="true"></i>
                  <i class="fa fa-circle-thin" aria-hidden="true"></i>
                  <i class="fa fa-circle-thin" aria-hidden="true"></i>
                  </a>
                  <?php } ?>
               </div>
               <?php } ?>
               <div class="checkbox checkbox-primary no-mtop checkbox-inline tour-add-edit-public">
                  <input type="checkbox" id="tour_is_public" name="is_public" <?php if(isset($tour)){if($tour->is_public == 1){echo 'checked';}}; ?>>
                  <label for="tour_is_public" data-toggle="tooltip" data-placement="bottom" title="<?php echo _l('tour_public_help'); ?>"><?php echo _l('tour_public'); ?></label>
               </div>
               <div class="checkbox checkbox-primary checkbox-inline tour-add-edit-billable">
                  <input type="checkbox" id="tour_is_billable" name="billable"
                     <?php if((isset($tour) && $tour->billable == 1) || (!isset($tour) && get_option('tour_biillable_checked_on_creation') == 1)) {echo ' checked'; }?>>
                  <label for="tour_is_billable"><?php echo _l('tour_billable'); ?></label>
               </div>
               <div class="tour-visible-to-customer checkbox checkbox-inline checkbox-primary<?php if((isset($tour) && $tour->rel_type != 'booking') || !isset($tour) || (isset($tour) && $tour->rel_type == 'booking' && total_rows(db_prefix().'booking_settings',array('booking_id'=>$tour->rel_id,'name'=>'view_tours','value'=>0)) > 0)){echo ' hide';} ?>">
                  <input type="checkbox" id="tour_visible_to_client" name="visible_to_client" <?php if(isset($tour)){if($tour->visible_to_client == 1){echo 'checked';}} ?>>
                  <label for="tour_visible_to_client"><?php echo _l('tour_visible_to_client'); ?></label>
               </div>
               <?php if(!isset($tour)){ ?>
               <a href="#" class="pull-right" onclick="slideToggle('#new-tour-attachments'); return false;">
               <?php echo _l('attach_files'); ?>
               </a>
               <div id="new-tour-attachments" class="hide">
                  <hr />
                  <div class="row attachments">
                     <div class="attachment">
                        <div class="col-md-12">
                           <div class="form-group">
                              <label for="attachment" class="control-label"><?php echo _l('add_tour_attachments'); ?></label>
                              <div class="input-group">
                                 <input type="file" extension="<?php echo str_replace('.','',get_option('allowed_files')); ?>" filesize="<?php echo file_upload_max_size(); ?>" class="form-control" name="attachments[0]">
                                 <span class="input-group-btn">
                                 <button class="btn btn-success add_more_attachments p8" type="button"><i class="fa fa-plus"></i></button>
                                 </span>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <?php
                  if($this->input->get('ticket_to_tour')) {
                    echo form_hidden('ticket_to_tour');
                  }
                  } ?>
               <hr />
               <?php $value = (isset($tour) ? $tour->name : ''); ?>
               <?php echo render_input('name','tour_add_edit_subject',$value); ?>
               <div class="tour-hours<?php if(isset($tour) && $tour->rel_type == 'booking' && total_rows(db_prefix().'bookings',array('id'=>$tour->rel_id,'billing_type'=>3)) == 0){echo ' hide';} ?>">
                  <?php $value = (isset($tour) ? $tour->hourly_rate : 0); ?>
                  <?php echo render_input('hourly_rate','tour_hourly_rate',$value); ?>
               </div>
               <div class="booking-details<?php if($rel_type != 'booking'){echo ' hide';} ?>">
                  <div class="form-group">
                     <label for="milestone"><?php echo _l('tour_milestone'); ?></label>
                     <select name="milestone" id="milestone" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        <option value=""></option>
                        <?php foreach($milestones as $milestone){ ?>
                        <option value="<?php echo $milestone['id']; ?>" <?php if(isset($tour) && $tour->milestone == $milestone['id']){echo 'selected'; } ?>><?php echo $milestone['name']; ?></option>
                        <?php } ?>
                     </select>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <?php if(isset($tour)){
                        $value = _d($tour->startdate);
                        } else if(isset($start_date)){
                        $value = $start_date;
                        } else {
                        $value = _d(date('Y-m-d'));
                        }
                        $date_attrs = array();
                        if(isset($tour) && $tour->recurring > 0 && $tour->last_recurring_date != null) {
                        $date_attrs['disabled'] = true;
                        }
                        ?>
                     <?php echo render_date_input('startdate','tour_add_edit_start_date',$value, $date_attrs); ?>
                  </div>
                  <div class="col-md-6">
                     <?php $value = (isset($tour) ? _d($tour->duedate) : ''); ?>
                     <?php echo render_date_input('duedate','tour_add_edit_due_date',$value,$booking_end_date_attrs); ?>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label for="priority" class="control-label"><?php echo _l('tour_add_edit_priority'); ?></label>
                        <select name="priority" class="selectpicker" id="priority" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <?php foreach(get_tours_priorities() as $priority) { ?>
                           <option value="<?php echo $priority['id']; ?>"<?php if(isset($tour) && $tour->priority == $priority['id'] || !isset($tour) && get_option('default_tour_priority') == $priority['id']){echo ' selected';} ?>><?php echo $priority['name']; ?></option>
                           <?php } ?>
                           <?php hooks()->do_action('tour_priorities_select', (isset($tour) ? $tour : 0)); ?>
                        </select>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                        <label for="repeat_every" class="control-label"><?php echo _l('tour_repeat_every'); ?></label>
                        <select name="repeat_every" id="repeat_every" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value=""></option>
                           <option value="1-week" <?php if(isset($tour) && $tour->repeat_every == 1 && $tour->recurring_type == 'week'){echo 'selected';} ?>><?php echo _l('week'); ?></option>
                           <option value="2-week" <?php if(isset($tour) && $tour->repeat_every == 2 && $tour->recurring_type == 'week'){echo 'selected';} ?>>2 <?php echo _l('weeks'); ?></option>
                           <option value="1-month" <?php if(isset($tour) && $tour->repeat_every == 1 && $tour->recurring_type == 'month'){echo 'selected';} ?>>1 <?php echo _l('month'); ?></option>
                           <option value="2-month" <?php if(isset($tour) && $tour->repeat_every == 2 && $tour->recurring_type == 'month'){echo 'selected';} ?>>2 <?php echo _l('months'); ?></option>
                           <option value="3-month" <?php if(isset($tour) && $tour->repeat_every == 3 && $tour->recurring_type == 'month'){echo 'selected';} ?>>3 <?php echo _l('months'); ?></option>
                           <option value="6-month" <?php if(isset($tour) && $tour->repeat_every == 6 && $tour->recurring_type == 'month'){echo 'selected';} ?>>6 <?php echo _l('months'); ?></option>
                           <option value="1-year" <?php if(isset($tour) && $tour->repeat_every == 1 && $tour->recurring_type == 'year'){echo 'selected';} ?>>1 <?php echo _l('year'); ?></option>
                           <option value="custom" <?php if(isset($tour) && $tour->custom_recurring == 1){echo 'selected';} ?>><?php echo _l('recurring_custom'); ?></option>
                        </select>
                     </div>
                  </div>
               </div>
               <div class="recurring_custom <?php if((isset($tour) && $tour->custom_recurring != 1) || (!isset($tour))){echo 'hide';} ?>">
                  <div class="row">
                     <div class="col-md-6">
                        <?php $value = (isset($tour) && $tour->custom_recurring == 1 ? $tour->repeat_every : 1); ?>
                        <?php echo render_input('repeat_every_custom','',$value,'number',array('min'=>1)); ?>
                     </div>
                     <div class="col-md-6">
                        <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value="day" <?php if(isset($tour) && $tour->custom_recurring == 1 && $tour->recurring_type == 'day'){echo 'selected';} ?>><?php echo _l('tour_recurring_days'); ?></option>
                           <option value="week" <?php if(isset($tour) && $tour->custom_recurring == 1 && $tour->recurring_type == 'week'){echo 'selected';} ?>><?php echo _l('tour_recurring_weeks'); ?></option>
                           <option value="month" <?php if(isset($tour) && $tour->custom_recurring == 1 && $tour->recurring_type == 'month'){echo 'selected';} ?>><?php echo _l('tour_recurring_months'); ?></option>
                           <option value="year" <?php if(isset($tour) && $tour->custom_recurring == 1 && $tour->recurring_type == 'year'){echo 'selected';} ?>><?php echo _l('tour_recurring_years'); ?></option>
                        </select>
                     </div>
                  </div>
               </div>
               <div id="cycles_wrapper" class="<?php if(!isset($tour) || (isset($tour) && $tour->recurring == 0)){echo ' hide';}?>">
                  <?php $value = (isset($tour) ? $tour->cycles : 0); ?>
                  <div class="form-group recurring-cycles">
                     <label for="cycles"><?php echo _l('recurring_total_cycles'); ?>
                     <?php if(isset($tour) && $tour->total_cycles > 0){
                        echo '<small>' . _l('cycles_passed', $tour->total_cycles) . '</small>';
                        }
                        ?>
                     </label>
                     <div class="input-group">
                        <input type="number" class="form-control"<?php if($value == 0){echo ' disabled'; } ?> name="cycles" id="cycles" value="<?php echo $value; ?>" <?php if(isset($tour) && $tour->total_cycles > 0){echo 'min="'.($tour->total_cycles).'"';} ?>>
                        <div class="input-group-addon">
                           <div class="checkbox">
                              <input type="checkbox"<?php if($value == 0){echo ' checked';} ?> id="unlimited_cycles">
                              <label for="unlimited_cycles"><?php echo _l('cycles_infinity'); ?></label>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <div class="form-group">
                        <label for="rel_type" class="control-label"><?php echo _l('tour_related_to'); ?></label>
                        <select name="rel_type" class="selectpicker" id="rel_type" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value=""></option>
                           <option value="booking"
                              <?php if(isset($tour) || $this->input->get('rel_type')){if($rel_type == 'booking'){echo 'selected';}} ?>><?php echo _l('booking'); ?></option>
                           <option value="invoice" <?php if(isset($tour) || $this->input->get('rel_type')){if($rel_type == 'invoice'){echo 'selected';}} ?>>
                              <?php echo _l('invoice'); ?>
                           </option>
                           <option value="customer"
                              <?php if(isset($tour) || $this->input->get('rel_type')){if($rel_type == 'customer'){echo 'selected';}} ?>>
                              <?php echo _l('client'); ?>
                           </option>
                           <option value="estimate" <?php if(isset($tour) || $this->input->get('rel_type')){if($rel_type == 'estimate'){echo 'selected';}} ?>>
                              <?php echo _l('estimate'); ?>
                           </option>
                           <option value="contract" <?php if(isset($tour) || $this->input->get('rel_type')){if($rel_type == 'contract'){echo 'selected';}} ?>>
                              <?php echo _l('contract'); ?>
                           </option>
                           <option value="ticket" <?php if(isset($tour) || $this->input->get('rel_type')){if($rel_type == 'ticket'){echo 'selected';}} ?>>
                              <?php echo _l('ticket'); ?>
                           </option>
                           <option value="expense" <?php if(isset($tour) || $this->input->get('rel_type')){if($rel_type == 'expense'){echo 'selected';}} ?>>
                              <?php echo _l('expense'); ?>
                           </option>
                           <option value="lead" <?php if(isset($tour) || $this->input->get('rel_type')){if($rel_type == 'lead'){echo 'selected';}} ?>>
                              <?php echo _l('lead'); ?>
                           </option>
                           <option value="quote" <?php if(isset($tour) || $this->input->get('rel_type')){if($rel_type == 'quote'){echo 'selected';}} ?>>
                              <?php echo _l('quote'); ?>
                           </option>
                        </select>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group<?php if($rel_id == ''){echo ' hide';} ?>" id="rel_id_wrapper">
                        <label for="rel_id" class="control-label"><span class="rel_id_label"></span></label>
                        <div id="rel_id_select">
                           <select name="rel_id" id="rel_id" class="ajax-sesarch" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <?php if($rel_id != '' && $rel_type != ''){
                              $rel_data = get_relation_data($rel_type,$rel_id);
                              $rel_val = get_relation_values($rel_data,$rel_type);
                              echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
                              } ?>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
               <?php
                  if(isset($tour)
                     && $tour->status == Tours_model::STATUS_COMPLETE
                     && (has_permission('create') || has_permission('edit'))){
                     echo render_datetime_input('datefinished','tour_finished',_dt($tour->datefinished));
                  }
               ?>
               <div class="form-group checklist-templates-wrapper<?php if(count($checklistTemplates) == 0 || isset($tour)){echo ' hide';}  ?>">
                  <label for="checklist_items"><?php echo _l('insert_checklist_templates'); ?></label>
                  <select id="checklist_items" name="checklist_items[]" class="selectpicker checklist-items-template-select" multiple="1" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex') ?>" data-width="100%" data-live-search="true" data-actions-box="true">
                     <option value="" class="hide"></option>
                     <?php foreach($checklistTemplates as $chkTemplate){ ?>
                     <option value="<?php echo $chkTemplate['id']; ?>">
                        <?php echo $chkTemplate['description']; ?>
                     </option>
                     <?php } ?>
                  </select>
               </div>
               <div class="form-group">
                  <div id="inputTagsWrapper">
                     <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                     <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($tour) ? prep_tags_input(get_tags_in($tour->id,'tour')) : ''); ?>" data-role="tagsinput">
                  </div>
               </div>
               <?php $rel_id_custom_field = (isset($tour) ? $tour->id : false); ?>
               <?php echo render_custom_fields('tours',$rel_id_custom_field); ?>
               <hr />
               <p class="bold"><?php echo _l('tour_add_edit_description'); ?></p>
               <?php
               // onclick and onfocus used for convert ticket to tour too
               echo render_textarea('description','',(isset($tour) ? $tour->description : ''),array('rows'=>6,'placeholder'=>_l('tour_add_description'),'data-tour-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($tour) || isset($tour) && $tour->description == '' ? 'init_editor(\'.tinymce-tour\', {height:200, auto_focus: true});' : '')),array(),'no-mbot','tinymce-tour'); ?>
            </div>
         </div>
      </div>
      <div class="modal-footer">
         <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
         <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
      </div>
   </div>
</div>
<?php echo form_close(); ?>
<script>
   var _rel_id = $('#rel_id'),
   _rel_type = $('#rel_type'),
   _rel_id_wrapper = $('#rel_id_wrapper'),
   data = {};

   var _milestone_selected_data;
   _milestone_selected_data = undefined;

   $(function(){

    $( "body" ).off( "change", "#rel_id" );

    var inner_popover_template = '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"></div></div></div>';

    $('#_tour_modal .tour-menu-options .trigger').popover({
      html: true,
      placement: "bottom",
      trigger: 'click',
      title:"<?php echo _l('actions'); ?>",
      content: function() {
       return $('body').find('#_tour_modal .tour-menu-options .content-menu').html();
     },
     template: inner_popover_template
   });

    custom_fields_hyperlink();

    appValidateForm($('#tour-form'), {
      name: 'required',
      startdate: 'required',
      repeat_every_custom: { min: 1},
    },tour_form_handler);

    $('.rel_id_label').html(_rel_type.find('option:selected').text());

    _rel_type.on('change', function() {

     var clonedSelect = _rel_id.html('').clone();
     _rel_id.selectpicker('destroy').remove();
     _rel_id = clonedSelect;
     $('#rel_id_select').append(clonedSelect);
     $('.rel_id_label').html(_rel_type.find('option:selected').text());

     tour_rel_select();
     if($(this).val() != ''){
      _rel_id_wrapper.removeClass('hide');
    } else {
      _rel_id_wrapper.addClass('hide');
    }
    init_booking_details(_rel_type.val());
   });

    init_datepicker();
    init_color_pickers();
    init_selectpicker();
    tour_rel_select();

    $('body').on('change','#rel_id',function(){
     if($(this).val() != ''){
       if(_rel_type.val() == 'booking'){
         $.get(admin_url + 'bookings/get_rel_booking_data/'+$(this).val()+'/'+tourid,function(booking){
           $("select[name='milestone']").html(booking.milestones);
           if(typeof(_milestone_selected_data) != 'undefined'){
            $("select[name='milestone']").val(_milestone_selected_data.id);
            $('input[name="duedate"]').val(_milestone_selected_data.due_date)
          }
          $("select[name='milestone']").selectpicker('refresh');
          if(booking.billing_type == 3){
           $('.tour-hours').addClass('booking-tour-hours');
         } else {
           $('.tour-hours').removeClass('booking-tour-hours');
         }

         if(booking.deadline) {
            var $duedate = $('#_tour_modal #duedate');
            var currentSelectedTourDate = $duedate.val();
            $duedate.attr('data-date-end-date', booking.deadline);
            $duedate.datetimepicker('destroy');
            init_datepicker($duedate);

            if(currentSelectedTourDate) {
               var dateTour = new Date(unformat_date(currentSelectedTourDate));
               var bookingDeadline = new Date(booking.deadline);
               if(dateTour > bookingDeadline) {
                  $duedate.val(booking.deadline_formatted);
               }
            }
         } else {
            reset_tour_duedate_input();
         }
         init_booking_details(_rel_type.val(),booking.allow_to_view_tours);
       },'json');
       } else {
         reset_tour_duedate_input();
       }
     }
   });

    <?php if(!isset($tour) && $rel_id != ''){ ?>
      _rel_id.change();
      <?php } ?>

    });

   <?php if(isset($_milestone_selected_data)){ ?>
    _milestone_selected_data = '<?php echo json_encode($_milestone_selected_data); ?>';
    _milestone_selected_data = JSON.parse(_milestone_selected_data);
    <?php } ?>

    function tour_rel_select(){
      var serverData = {};
      serverData.rel_id = _rel_id.val();
      data.type = _rel_type.val();
      init_ajax_search(_rel_type.val(),_rel_id,serverData);
     }

     function init_booking_details(type,tours_visible_to_customer){
      var wrap = $('.non-booking-details');
      var wrap_tour_hours = $('.tour-hours');
      if(type == 'booking'){
        if(wrap_tour_hours.hasClass('booking-tour-hours') == true){
          wrap_tour_hours.removeClass('hide');
        } else {
          wrap_tour_hours.addClass('hide');
        }
        wrap.addClass('hide');
        $('.booking-details').removeClass('hide');
      } else {
        wrap_tour_hours.removeClass('hide');
        wrap.removeClass('hide');
        $('.booking-details').addClass('hide');
        $('.tour-visible-to-customer').addClass('hide').prop('checked',false);
      }
      if(typeof(tours_visible_to_customer) != 'undefined'){
        if(tours_visible_to_customer == 1){
          $('.tour-visible-to-customer').removeClass('hide');
          $('.tour-visible-to-customer input').prop('checked',true);
        } else {
          $('.tour-visible-to-customer').addClass('hide')
          $('.tour-visible-to-customer input').prop('checked',false);
        }
      }
    }
    function reset_tour_duedate_input() {
      var $duedate = $('#_tour_modal #duedate');
      $duedate.removeAttr('data-date-end-date');
      $duedate.datetimepicker('destroy');
      init_datepicker($duedate);
   }
</script>
