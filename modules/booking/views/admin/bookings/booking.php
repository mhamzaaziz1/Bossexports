<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php echo form_open($this->uri->uri_string(),array('id'=>'booking_form')); ?>
            <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                        </h4>
                        <hr class="hr-panel-heading" />
                        <?php
                        $disable_type_edit = '';
                        if(isset($booking)){
                            if($booking->billing_type != 1){
                                if(total_rows(db_prefix().'tours',array('rel_id'=>$booking->id,'rel_type'=>'booking','billable'=>1,'billed'=>1)) > 0){
                                    $disable_type_edit = 'disabled';
                                }
                            }
                        }
                        ?>
                        <?php $value = (isset($booking) ? $booking->name : ''); ?>
                        <?php echo render_input('name','booking_name',$value); ?>
                        <div class="form-group select-placeholder">
                            <label for="clientid" class="control-label"><?php echo _l('booking_customer'); ?></label>
                            <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                             <?php $selected = (isset($booking) ? $booking->clientid : '');
                             if($selected == ''){
                                 $selected = (isset($customer_id) ? $customer_id: '');
                             }
                             if($selected != ''){
                                $rel_data = get_relation_data('customer',$selected);
                                $rel_val = get_relation_values($rel_data,'customer');
                                echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
                            } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="checkbox checkbox-success">
                            <input type="checkbox" <?php if((isset($booking) && $booking->progress_from_tours == 1) || !isset($booking)){echo 'checked';} ?> name="progress_from_tours" id="progress_from_tours">
                            <label for="progress_from_tours"><?php echo _l('calculate_progress_through_tours'); ?></label>
                        </div>
                    </div>
                    <?php
                    if(isset($booking) && $booking->progress_from_tours == 1){
                        $value = $this->bookings_model->calc_progress_by_tours($booking->id);
                    } else if(isset($booking) && $booking->progress_from_tours == 0){
                        $value = $booking->progress;
                    } else {
                        $value = 0;
                    }
                    ?>
                    <label for=""><?php echo _l('booking_progress'); ?> <span class="label_progress"><?php echo $value; ?>%</span></label>
                    <?php echo form_hidden('progress',$value); ?>
                    <div class="booking_progress_slider booking_progress_slider_horizontal mbot15"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group select-placeholder">
                                <label for="billing_type"><?php echo _l('booking_billing_type'); ?></label>
                                <div class="clearfix"></div>
                                <select name="billing_type" class="selectpicker" id="billing_type" data-width="100%" <?php echo $disable_type_edit ; ?> data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""></option>
                                    <option value="1" <?php if(isset($booking) && $booking->billing_type == 1 || !isset($booking) && $auto_select_billing_type && $auto_select_billing_type->billing_type == 1){echo 'selected'; } ?>><?php echo _l('booking_billing_type_fixed_cost'); ?></option>
                                    <option value="2" <?php if(isset($booking) && $booking->billing_type == 2 || !isset($booking) && $auto_select_billing_type && $auto_select_billing_type->billing_type == 2){echo 'selected'; } ?>><?php echo _l('booking_billing_type_booking_hours'); ?></option>
                                    <option value="3" data-subtext="<?php echo _l('booking_billing_type_booking_tour_hours_hourly_rate'); ?>" <?php if(isset($booking) && $booking->billing_type == 3 || !isset($booking) && $auto_select_billing_type && $auto_select_billing_type->billing_type == 3){echo 'selected'; } ?>><?php echo _l('booking_billing_type_booking_tour_hours'); ?></option>
                                </select>
                                <?php if($disable_type_edit != ''){
                                    echo '<p class="text-danger">'._l('cant_change_billing_type_billed_tours_found').'</p>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group select-placeholder">
                                <label for="status"><?php echo _l('booking_status'); ?></label>
                                <div class="clearfix"></div>
                                <select name="status" id="status" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <?php foreach($statuses as $status){ ?>
                                        <option value="<?php echo $status['id']; ?>" <?php if(!isset($booking) && $status['id'] == 2 || (isset($booking) && $booking->status == $status['id'])){echo 'selected';} ?>><?php echo $status['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php if(isset($booking) && booking_has_recurring_tours($booking->id)) { ?>
                        <div class="alert alert-warning recurring-tours-notice hide"></div>
                    <?php } ?>
                    <?php if(is_email_template_active('booking-finished-to-customer')){ ?>
                        <div class="form-group booking_marked_as_finished hide">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="booking_marked_as_finished_email_to_contacts" id="booking_marked_as_finished_email_to_contacts">
                                <label for="booking_marked_as_finished_email_to_contacts"><?php echo _l('booking_marked_as_finished_to_contacts'); ?></label>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if(isset($booking)){ ?>
                        <div class="form-group mark_all_tours_as_completed hide">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="mark_all_tours_as_completed" id="mark_all_tours_as_completed">
                                <label for="mark_all_tours_as_completed"><?php echo _l('booking_mark_all_tours_as_completed'); ?></label>
                            </div>
                        </div>
                        <div class="notify_booking_members_status_change hide">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="notify_booking_members_status_change" id="notify_booking_members_status_change">
                                <label for="notify_booking_members_status_change"><?php echo _l('notify_booking_members_status_change'); ?></label>
                            </div>
                            <hr />
                        </div>
                    <?php } ?>
                    <?php
                    $input_field_hide_class_total_cost = '';
                    if(!isset($booking)){
                        if($auto_select_billing_type && $auto_select_billing_type->billing_type != 1 || !$auto_select_billing_type){
                            $input_field_hide_class_total_cost = 'hide';
                        }
                    } else if(isset($booking) && $booking->billing_type != 1){
                        $input_field_hide_class_total_cost = 'hide';
                    }
                    ?>
                    <div id="booking_cost" class="<?php echo $input_field_hide_class_total_cost; ?>">
                        <?php $value = (isset($booking) ? $booking->booking_cost : ''); ?>
                        <?php echo render_input('booking_cost','booking_total_cost',$value,'number'); ?>
                    </div>
                    <?php
                    $input_field_hide_class_rate_per_hour = '';
                    if(!isset($booking)){
                        if($auto_select_billing_type && $auto_select_billing_type->billing_type != 2 || !$auto_select_billing_type){
                            $input_field_hide_class_rate_per_hour = 'hide';
                        }
                    } else if(isset($booking) && $booking->billing_type != 2){
                        $input_field_hide_class_rate_per_hour = 'hide';
                    }
                    ?>
                    <div id="booking_rate_per_hour" class="<?php echo $input_field_hide_class_rate_per_hour; ?>">
                        <?php $value = (isset($booking) ? $booking->booking_rate_per_hour : ''); ?>
                        <?php
                        $input_disable = array();
                        if($disable_type_edit != ''){
                            $input_disable['disabled'] = true;
                        }
                        ?>
                        <?php echo render_input('booking_rate_per_hour','booking_rate_per_hour',$value,'number',$input_disable); ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_input('estimated_hours','estimated_hours',isset($booking) ? $booking->estimated_hours : '','number'); ?>
                        </div>
                        <div class="col-md-6">
                         <?php
                         $selected = array();
                         if(isset($booking_members)){
                            foreach($booking_members as $member){
                                array_push($selected,$member['staff_id']);
                            }
                        } else {
                            array_push($selected,get_staff_user_id());
                        }
                        echo render_select('booking_members[]',$staff,array('staffid',array('firstname','lastname')),'booking_members',$selected,array('multiple'=>true,'data-actions-box'=>true),array(),'','',false);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?php $value = (isset($booking) ? _d($booking->start_date) : _d(date('Y-m-d'))); ?>
                        <?php echo render_date_input('start_date','booking_start_date',$value); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $value = (isset($booking) ? _d($booking->deadline) : ''); ?>
                        <?php echo render_date_input('deadline','booking_deadline',$value); ?>
                    </div>
                </div>
                <?php if(isset($booking) && $booking->date_finished != null && $booking->status == 4) { ?>
                    <?php echo render_datetime_input('date_finished','booking_completed_date',_dt($booking->date_finished)); ?>
                <?php } ?>
                <div class="form-group">
                    <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                    <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($booking) ? prep_tags_input(get_tags_in($booking->id,'booking')) : ''); ?>" data-role="tagsinput">
                </div>
                <?php $rel_id_custom_field = (isset($booking) ? $booking->id : false); ?>
                <?php echo render_custom_fields('bookings',$rel_id_custom_field); ?>
                <p class="bold"><?php echo _l('booking_description'); ?></p>
                <?php $contents = ''; if(isset($booking)){$contents = $booking->description;} ?>
                <?php echo render_textarea('description','',$contents,array(),array(),'','tinymce'); ?>
                <?php if(is_email_template_active('assigned-to-booking')){ ?>
                    <div class="checkbox checkbox-primary">
                     <input type="checkbox" name="send_created_email" id="send_created_email">
                     <label for="send_created_email"><?php echo _l('booking_send_created_email'); ?></label>
                 </div>
             <?php } ?>
             <div class="btn-bottom-toolbar text-right">
                 <button type="submit" data-form="#booking_form" class="btn btn-info" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('submit'); ?></button>
             </div>
         </div>
     </div>
 </div>
 <div class="col-md-5">
    <div class="panel_s">
        <div class="panel-body" id="booking-settings-area">
           <h4 class="no-margin">
               <?php echo _l('booking_settings'); ?>
           </h4>
           <hr class="hr-panel-heading" />
           <?php foreach($settings as $setting){

            $checked = ' checked';
            if(isset($booking)){
                if($booking->settings->{$setting} == 0){
                    $checked = '';
                }
            } else {
                foreach($last_booking_settings as $last_setting) {
                    if($setting == $last_setting['name']){
                        // hide_tours_on_main_tours_table is not applied on most used settings to prevent confusions
                        if($last_setting['value'] == 0 || $last_setting['name'] == 'hide_tours_on_main_tours_table'){
                            $checked = '';
                        }
                    }
                }
                if(count($last_booking_settings) == 0 && $setting == 'hide_tours_on_main_tours_table') {
                    $checked = '';
                }
            } ?>
            <?php if($setting != 'available_features'){ ?>
                <div class="checkbox">
                    <input type="checkbox" name="settings[<?php echo $setting; ?>]" <?php echo $checked; ?> id="<?php echo $setting; ?>">
                    <label for="<?php echo $setting; ?>">
                        <?php if($setting == 'hide_tours_on_main_tours_table'){ ?>
                            <?php echo _l('hide_tours_on_main_tours_table'); ?>
                        <?php } else{ ?>
                            <?php echo _l('booking_allow_client_to',_l('booking_setting_'.$setting)); ?>
                        <?php } ?>
                    </label>
                </div>
            <?php } else { ?>
                <div class="form-group mtop15 select-placeholder booking-available-features">
                    <label for="available_features"><?php echo _l('visible_tabs'); ?></label>
                    <select name="settings[<?php echo $setting; ?>][]" id="<?php echo $setting; ?>" multiple="true" class="selectpicker" id="available_features" data-width="100%" data-actions-box="true" data-hide-disabled="true">
                        <?php foreach(get_booking_tabs_admin() as $tab) {
                            $selected = '';
                            if(isset($tab['collapse'])){ ?>
                                <optgroup label="<?php echo $tab['name']; ?>">
                                    <?php foreach($tab['children'] as $tab_dropdown) {
                                        $selected = '';
                                        if(isset($booking) && (
                                            (isset($booking->settings->available_features[$tab_dropdown['slug']])
                                                && $booking->settings->available_features[$tab_dropdown['slug']] == 1)
                                            || !isset($booking->settings->available_features[$tab_dropdown['slug']]))) {
                                            $selected = ' selected';
                                    } else if(!isset($booking) && count($last_booking_settings) > 0) {
                                        foreach($last_booking_settings as $last_booking_setting) {
                                            if($last_booking_setting['name'] == $setting) {
                                                if(isset($last_booking_setting['value'][$tab_dropdown['slug']])
                                                    && $last_booking_setting['value'][$tab_dropdown['slug']] == 1) {
                                                    $selected = ' selected';
                                            }
                                        }
                                    }
                                } else if(!isset($booking)) {
                                    $selected = ' selected';
                                }
                                ?>
                                <option value="<?php echo $tab_dropdown['slug']; ?>"<?php echo $selected; ?><?php if(isset($tab_dropdown['linked_to_customer_option']) && is_array($tab_dropdown['linked_to_customer_option']) && count($tab_dropdown['linked_to_customer_option']) > 0){ ?> data-linked-customer-option="<?php echo implode(',',$tab_dropdown['linked_to_customer_option']); ?>"<?php } ?>><?php echo $tab_dropdown['name']; ?></option>
                            <?php } ?>
                        </optgroup>
                    <?php } else {
                        if(isset($booking) && (
                            (isset($booking->settings->available_features[$tab['slug']])
                             && $booking->settings->available_features[$tab['slug']] == 1)
                            || !isset($booking->settings->available_features[$tab['slug']]))) {
                            $selected = ' selected';
                    } else if(!isset($booking) && count($last_booking_settings) > 0) {
                        foreach($last_booking_settings as $last_booking_setting) {
                            if($last_booking_setting['name'] == $setting) {
                                if(isset($last_booking_setting['value'][$tab['slug']])
                                    && $last_booking_setting['value'][$tab['slug']] == 1) {
                                    $selected = ' selected';
                            }
                        }
                    }
                } else if(!isset($booking)) {
                    $selected = ' selected';
                }
                ?>
                <option value="<?php echo $tab['slug']; ?>"<?php if($tab['slug'] =='booking_overview'){echo ' disabled selected';} ?>
                <?php echo $selected; ?>
                <?php if(isset($tab['linked_to_customer_option']) && is_array($tab['linked_to_customer_option']) && count($tab['linked_to_customer_option']) > 0){ ?> data-linked-customer-option="<?php echo implode(',',$tab['linked_to_customer_option']); ?>"<?php } ?>>
                <?php echo $tab['name']; ?>
            </option>
        <?php } ?>
    <?php } ?>
</select>
</div>
<?php } ?>
<hr class="no-margin" />
<?php } ?>
</div>
</div>
</div>
<?php echo form_close(); ?>
</div>
<div class="btn-bottom-pusher"></div>
</div>
</div>
<?php init_tail(); ?>
<script>
    <?php if(isset($booking)){ ?>
        var original_booking_status = '<?php echo $booking->status; ?>';
    <?php } ?>
    $(function(){

        $('select[name="billing_type"]').on('change',function(){
            var type = $(this).val();
            if(type == 1){
                $('#booking_cost').removeClass('hide');
                $('#booking_rate_per_hour').addClass('hide');
            } else if(type == 2){
                $('#booking_cost').addClass('hide');
                $('#booking_rate_per_hour').removeClass('hide');
            } else {
                $('#booking_cost').addClass('hide');
                $('#booking_rate_per_hour').addClass('hide');
            }
        });

        appValidateForm($('form'),{name:'required',clientid:'required',start_date:'required',billing_type:'required'});

        $('select[name="status"]').on('change',function(){
            var status = $(this).val();
            var mark_all_tours_completed = $('.mark_all_tours_as_completed');
            var notify_booking_members_status_change = $('.notify_booking_members_status_change');
            mark_all_tours_completed.removeClass('hide');
            if(typeof(original_booking_status) != 'undefined'){
                if(original_booking_status != status){

                    mark_all_tours_completed.removeClass('hide');
                    notify_booking_members_status_change.removeClass('hide');

                    if(status == 4 || status == 5 || status == 3) {
                        $('.recurring-tours-notice').removeClass('hide');
                        var notice = "<?php echo _l('booking_changing_status_recurring_tours_notice'); ?>";
                        notice = notice.replace('{0}', $(this).find('option[value="'+status+'"]').text().trim());
                        $('.recurring-tours-notice').html(notice);
                        $('.recurring-tours-notice').append('<input type="hidden" name="cancel_recurring_tours" value="true">');
                        mark_all_tours_completed.find('input').prop('checked',true);
                    } else {
                        $('.recurring-tours-notice').html('').addClass('hide');
                        mark_all_tours_completed.find('input').prop('checked',false);
                    }
                } else {
                    mark_all_tours_completed.addClass('hide');
                    mark_all_tours_completed.find('input').prop('checked',false);
                    notify_booking_members_status_change.addClass('hide');
                    $('.recurring-tours-notice').html('').addClass('hide');
                }
            }

            if(status == 4){
                $('.booking_marked_as_finished').removeClass('hide');
            } else {
                $('.booking_marked_as_finished').addClass('hide');
                $('.booking_marked_as_finished').prop('checked',false);
            }
        });

        $('form').on('submit',function(){
            $('select[name="billing_type"]').prop('disabled',false);
            $('#available_features,#available_features option').prop('disabled',false);
            $('input[name="booking_rate_per_hour"]').prop('disabled',false);
        });

        var progress_input = $('input[name="progress"]');
        var progress_from_tours = $('#progress_from_tours');
        var progress = progress_input.val();

        $('.booking_progress_slider').slider({
            min:0,
            max:100,
            value:progress,
            disabled:progress_from_tours.prop('checked'),
            slide: function( event, ui ) {
                progress_input.val( ui.value );
                $('.label_progress').html(ui.value+'%');
            }
        });

        progress_from_tours.on('change',function(){
            var _checked = $(this).prop('checked');
            $('.booking_progress_slider').slider({disabled:_checked});
        });

        $('#booking-settings-area input').on('change',function(){
            if($(this).attr('id') == 'view_tours' && $(this).prop('checked') == false){
                $('#create_tours').prop('checked',false).prop('disabled',true);
                $('#edit_tours').prop('checked',false).prop('disabled',true);
                $('#view_tour_comments').prop('checked',false).prop('disabled',true);
                $('#comment_on_tours').prop('checked',false).prop('disabled',true);
                $('#view_tour_attachments').prop('checked',false).prop('disabled',true);
                $('#view_tour_checklist_items').prop('checked',false).prop('disabled',true);
                $('#upload_on_tours').prop('checked',false).prop('disabled',true);
                $('#view_tour_total_logged_time').prop('checked',false).prop('disabled',true);
            } else if($(this).attr('id') == 'view_tours' && $(this).prop('checked') == true){
                $('#create_tours').prop('disabled',false);
                $('#edit_tours').prop('disabled',false);
                $('#view_tour_comments').prop('disabled',false);
                $('#comment_on_tours').prop('disabled',false);
                $('#view_tour_attachments').prop('disabled',false);
                $('#view_tour_checklist_items').prop('disabled',false);
                $('#upload_on_tours').prop('disabled',false);
                $('#view_tour_total_logged_time').prop('disabled',false);
            }
        });

            // Auto adjust customer permissions based on selected booking visible tabs
            // Eq Booking creator disable TOURS tab, then this function will auto turn off customer booking option Allow customer to view tours

            $('#available_features').on('change',function(){
                $("#available_features option").each(function(){
                 if($(this).data('linked-customer-option') && !$(this).is(':selected')) {
                    var opts = $(this).data('linked-customer-option').split(',');
                    for(var i = 0; i<opts.length;i++) {
                        var booking_option = $('#'+opts[i]);
                        booking_option.prop('checked',false);
                        if(opts[i] == 'view_tours') {
                            booking_option.trigger('change');
                        }
                    }
                }
            });
            });
            $("#view_tours").trigger('change');
            <?php if(!isset($booking)) { ?>
                $('#available_features').trigger('change');
            <?php } ?>
        });
    </script>
</body>
</html>
