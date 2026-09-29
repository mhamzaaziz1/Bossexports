<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
   <div class="col-md-6 border-right booking-overview-left">
      <div class="row">
       <div class="col-md-12">
         <p class="booking-info bold font-size-14">
            <?php echo _l('overview'); ?>
         </p>
      </div>
      <?php if(count($booking->shared_vault_entries) > 0){ ?>
      <?php $this->load->view('admin/clients/vault_confirm_password'); ?>
      <div class="col-md-12">
         <p class="bold">
           <a href="#" onclick="slideToggle('#booking_vault_entries'); return false;">
             <i class="fa fa-cloud"></i> <?php echo _l('booking_shared_vault_entry_login_details'); ?>
          </a>
       </p>
       <div id="booking_vault_entries" class="hide">
         <?php foreach($booking->shared_vault_entries as $vault_entry){ ?>
         <div class="row" id="<?php echo 'vaultEntry-'.$vault_entry['id']; ?>">
            <div class="col-md-6">
               <p class="mtop5">
                  <b><?php echo _l('server_address'); ?>: </b><?php echo $vault_entry['server_address']; ?>
               </p>
               <p>
                  <b><?php echo _l('port'); ?>: </b><?php echo !empty($vault_entry['port']) ? $vault_entry['port'] : _l('no_port_provided'); ?>
               </p>
               <p>
                  <b><?php echo _l('vault_username'); ?>: </b><?php echo $vault_entry['username']; ?>
               </p>
               <p class="no-margin">
                  <b><?php echo _l('vault_password'); ?>: </b><span class="vault-password-fake">
                     <?php echo str_repeat('&bull;',10);?>  </span><span class="vault-password-encrypted"></span> <a href="#" class="vault-view-password mleft10" data-toggle="tooltip" data-title="<?php echo _l('view_password'); ?>" onclick="vault_re_enter_password(<?php echo $vault_entry['id']; ?>,this); return false;"><i class="fa fa-lock" aria-hidden="true"></i></a>
                  </p>
               </div>
               <div class="col-md-6">
                  <?php if(!empty($vault_entry['description'])){ ?>
                  <p>
                     <b><?php echo _l('vault_description'); ?>: </b><br /><?php echo $vault_entry['description']; ?>
                  </p>
                  <?php } ?>
               </div>
            </div>
            <hr class="hr-10" />
            <?php } ?>
         </div>
         <hr class="hr-panel-heading booking-area-separation" />
      </div>
      <?php } ?>
      <div class="col-md-7">
         <table class="table no-margin booking-overview-table">
            <tbody>
              <tr class="booking-overview-id">
                  <td class="bold"><?php echo _l('booking'); ?> <?php echo _l('the_number_sign'); ?></td>
                  <td>
                      <?php echo $booking->id; ?>
                  </td>
              </tr>
              <tr class="booking-overview-customer">
                  <td class="bold"><?php echo _l('booking_customer'); ?></td>
                  <td>
                      <a href="<?php echo admin_url(); ?>booking/clients/client/<?php echo $booking->clientid; ?>">
                        <?php echo $booking->client_data->company; ?>
                      </a>
                  </td>
              </tr>
               <?php if(has_permission('bookings','','edit')){ ?>
               <tr class="booking-overview-billing">
                  <td class="bold"><?php echo _l('booking_billing_type'); ?></td>
                  <td>
                     <?php
                     if($booking->billing_type == 1){
                       $type_name = 'booking_billing_type_fixed_cost';
                    } else if($booking->billing_type == 2){
                       $type_name = 'booking_billing_type_booking_hours';
                    } else {
                       $type_name = 'booking_billing_type_booking_tour_hours';
                    }
                    echo _l($type_name);
                    ?>
                 </td>
                 <?php if($booking->billing_type == 1 || $booking->billing_type == 2){
                  echo '<tr class="booking-overview-amount">';
                  if($booking->billing_type == 1){
                    echo '<td class="bold">'._l('booking_total_cost').'</td>';
                    echo '<td>'.app_format_money($booking->booking_cost, $currency).'</td>';
                 } else {
                    echo '<td class="bold">'._l('booking_rate_per_hour').'</td>';
                    echo '<td>'.app_format_money($booking->booking_rate_per_hour, $currency).'</td>';
                 }
                 echo '<tr>';
              }
           }
           ?>
           <tr class="booking-overview-status">
            <td class="bold"><?php echo _l('booking_status'); ?></td>
            <td><?php echo $booking_status['name']; ?></td>
         </tr>
         <tr class="booking-overview-date-created">
            <td class="bold"><?php echo _l('booking_datecreated'); ?></td>
            <td><?php echo _d($booking->booking_created); ?></td>
         </tr>
         <tr class="booking-overview-start-date">
            <td class="bold"><?php echo _l('booking_start_date'); ?></td>
            <td><?php echo _d($booking->start_date); ?></td>
         </tr>
         <?php if($booking->deadline){ ?>
         <tr class="booking-overview-deadline">
            <td class="bold"><?php echo _l('booking_deadline'); ?></td>
            <td><?php echo _d($booking->deadline); ?></td>
         </tr>
         <?php } ?>
         <?php if($booking->date_finished){ ?>
         <tr class="booking-overview-date-finished">
            <td class="bold"><?php echo _l('booking_completed_date'); ?></td>
            <td class="text-success"><?php echo _dt($booking->date_finished); ?></td>
         </tr>
         <?php } ?>
         <?php if($booking->estimated_hours && $booking->estimated_hours != '0'){ ?>
         <tr class="booking-overview-estimated-hours">
            <td class="bold<?php if(hours_to_seconds_format($booking->estimated_hours) < (int)$booking_total_logged_time){echo ' text-warning';} ?>"><?php echo _l('estimated_hours'); ?></td>
            <td><?php echo str_replace('.', ':', $booking->estimated_hours); ?></td>
         </tr>
         <?php } ?>
         <?php if(has_permission('bookings','','create')){ ?>
         <tr class="booking-overview-total-logged-hours">
            <td class="bold"><?php echo _l('booking_overview_total_logged_hours'); ?></td>
            <td><?php echo seconds_to_time_format($booking_total_logged_time); ?></td>
         </tr>
         <?php } ?>
         <?php $custom_fields = get_custom_fields('bookings');
         if(count($custom_fields) > 0){ ?>
         <?php foreach($custom_fields as $field){ ?>
         <?php $value = get_custom_field_value($booking->id,$field['id'],'bookings');
         if($value == ''){continue;} ?>
         <tr>
            <td class="bold"><?php echo ucfirst($field['name']); ?></td>
            <td><?php echo $value; ?></td>
         </tr>
         <?php } ?>
         <?php } ?>
      </tbody>
   </table>
</div>
<div class="col-md-5 text-center booking-percent-col mtop10">
   <p class="bold"><?php echo _l('booking_progress_text'); ?></p>
   <div class="booking-progress relative mtop15" data-value="<?php echo $percent_circle; ?>" data-size="150" data-thickness="22" data-reverse="true">
      <strong class="booking-percent"></strong>
   </div>
</div>
</div>
<?php $tags = get_tags_in($booking->id,'booking'); ?>
<?php if(count($tags) > 0){ ?>
<div class="clearfix"></div>
<div class="tags-read-only-custom booking-overview-tags">
   <hr class="hr-panel-heading booking-area-separation hr-10" />
   <?php echo '<p class="font-size-14"><b><i class="fa fa-tag" aria-hidden="true"></i> ' . _l('tags') . ':</b></p>'; ?>
   <input type="text" class="tagsinput read-only" id="tags" name="tags" value="<?php echo prep_tags_input($tags); ?>" data-role="tagsinput">
</div>
<div class="clearfix"></div>
<?php } ?>
<div class="tc-content booking-overview-description">
   <hr class="hr-panel-heading booking-area-separation" />
   <p class="bold font-size-14 booking-info"><?php echo _l('booking_description'); ?></p>
   <?php if(empty($booking->description)){
      echo '<p class="text-muted no-mbot mtop15">' . _l('no_description_booking') . '</p>';
   }
   echo check_for_links($booking->description); ?>
</div>
<div class="team-members booking-overview-team-members">
   <hr class="hr-panel-heading booking-area-separation" />
   <?php if(has_permission('bookings','','edit')){ ?>
   <div class="inline-block pull-right mright10 booking-member-settings" data-toggle="tooltip" data-title="<?php echo _l('add_edit_members'); ?>">
      <a href="#" data-toggle="modal" class="pull-right" data-target="#add-edit-members"><i class="fa fa-cog"></i></a>
   </div>
   <?php } ?>
   <p class="bold font-size-14 booking-info">
      <?php echo _l('booking_members'); ?>
   </p>
   <div class="clearfix"></div>
   <?php
   if(count($members) == 0){
      echo '<p class="text-muted mtop10 no-mbot">'._l('no_booking_members').'</p>';
   }
   foreach($members as $member){ ?>
   <div class="media">
      <div class="media-left">
         <a href="<?php echo admin_url('profile/'.$member["staff_id"]); ?>">
            <?php echo staff_profile_image($member['staff_id'],array('staff-profile-image-small','media-object')); ?>
         </a>
      </div>
      <div class="media-body">
         <?php if(has_permission('bookings','','edit')){ ?>
         <a href="<?php echo admin_url('booking/bookings/remove_team_member/'.$booking->id.'/'.$member['staff_id']); ?>" class="pull-right text-danger _delete"><i class="fa fa fa-times"></i></a>
         <?php } ?>
         <h5 class="media-heading mtop5"><a href="<?php echo admin_url('profile/'.$member["staff_id"]); ?>"><?php echo get_staff_full_name($member['staff_id']); ?></a>
            <?php if(has_permission('bookings','','create') || $member['staff_id'] == get_staff_user_id()){ ?>
            <br /><small class="text-muted"><?php echo _l('total_logged_hours_by_staff') .': '.seconds_to_time_format($member['total_logged_time']); ?></small>
            <?php } ?>
         </h5>
      </div>
   </div>
   <?php } ?>
</div>
</div>
<div class="col-md-6 booking-overview-right">
   <div class="row">
      <div class="col-md-<?php echo ($booking->deadline ? 6 : 12); ?> booking-progress-bars">
         <div class="row">
           <div class="booking-overview-open-tours">
            <div class="col-md-9">
               <p class="text-uppercase bold text-dark font-medium">
                  <span dir="ltr"><?php echo $tours_not_completed; ?> / <?php echo $total_tours; ?></span>
                  <?php echo _l('booking_open_tours'); ?>
               </p>
               <p class="text-muted bold"><?php echo $tours_not_completed_progress; ?>%</p>
            </div>
            <div class="col-md-3 text-right">
               <i class="fa fa-check-circle<?php if($tours_not_completed_progress >= 100){echo ' text-success';} ?>" aria-hidden="true"></i>
            </div>
            <div class="col-md-12 mtop5">
               <div class="progress no-margin progress-bar-mini">
                  <div class="progress-bar progress-bar-success no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $tours_not_completed_progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $tours_not_completed_progress; ?>">
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php if($booking->deadline){ ?>
   <div class="col-md-6 booking-progress-bars booking-overview-days-left">
      <div class="row">
         <div class="col-md-9">
            <p class="text-uppercase bold text-dark font-medium">
               <span dir="ltr"><?php echo $booking_days_left; ?> / <?php echo $booking_total_days; ?></span>
               <?php echo _l('booking_days_left'); ?>
            </p>
            <p class="text-muted bold"><?php echo $booking_time_left_percent; ?>%</p>
         </div>
         <div class="col-md-3 text-right">
            <i class="fa fa-calendar-check-o<?php if($booking_time_left_percent >= 100){echo ' text-success';} ?>" aria-hidden="true"></i>
         </div>
         <div class="col-md-12 mtop5">
            <div class="progress no-margin progress-bar-mini">
               <div class="progress-bar<?php if($booking_time_left_percent == 0){echo ' progress-bar-warning ';} else { echo ' progress-bar-success ';} ?>no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $booking_time_left_percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $booking_time_left_percent; ?>">
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php } ?>
</div>
<hr class="hr-panel-heading" />

<?php if(has_permission('bookings','','create')) { ?>
<div class="row">
   <?php if($booking->billing_type == 3 || $booking->billing_type == 2){ ?>
   <div class="col-md-12 booking-overview-logged-hours-finance">
      <div class="col-md-3">
         <?php
         $data = $this->bookings_model->total_logged_time_by_billing_type($booking->id);
         ?>
         <p class="text-uppercase text-muted"><?php echo _l('booking_overview_logged_hours'); ?> <span class="bold"><?php echo $data['logged_time']; ?></span></p>
         <p class="bold font-medium"><?php echo app_format_money($data['total_money'], $currency); ?></p>
      </div>
      <div class="col-md-3">
         <?php
         $data = $this->bookings_model->data_billable_time($booking->id);
         ?>
         <p class="text-uppercase text-info"><?php echo _l('booking_overview_billable_hours'); ?> <span class="bold"><?php echo $data['logged_time'] ?></span></p>
         <p class="bold font-medium"><?php echo app_format_money($data['total_money'], $currency); ?></p>
      </div>
      <div class="col-md-3">
         <?php
         $data = $this->bookings_model->data_billed_time($booking->id);
         ?>
         <p class="text-uppercase text-success"><?php echo _l('booking_overview_billed_hours'); ?> <span class="bold"><?php echo $data['logged_time']; ?></span></p>
         <p class="bold font-medium"><?php echo app_format_money($data['total_money'], $currency); ?></p>
      </div>
      <div class="col-md-3">
         <?php
         $data = $this->bookings_model->data_unbilled_time($booking->id);
         ?>
         <p class="text-uppercase text-danger"><?php echo _l('booking_overview_unbilled_hours'); ?> <span class="bold"><?php echo $data['logged_time']; ?></span></p>
         <p class="bold font-medium"><?php echo app_format_money($data['total_money'], $currency); ?></p>
      </div>
      <div class="clearfix"></div>
      <hr class="hr-panel-heading" />
   </div>
   <?php } ?>
</div>
<div class="row">
   <div class="col-md-12 booking-overview-expenses-finance">
      <div class="col-md-3">
         <p class="text-uppercase text-muted"><?php echo _l('booking_overview_expenses'); ?></p>
         <p class="bold font-medium"><?php echo app_format_money(sum_from_table(db_prefix().'expenses',array('where'=>array('booking_id'=>$booking->id),'field'=>'amount')), $currency); ?></p>
      </div>
      <div class="col-md-3">
         <p class="text-uppercase text-info"><?php echo _l('booking_overview_expenses_billable'); ?></p>
         <p class="bold font-medium"><?php echo app_format_money(sum_from_table(db_prefix().'expenses',array('where'=>array('booking_id'=>$booking->id,'billable'=>1),'field'=>'amount')), $currency); ?></p>
      </div>
      <div class="col-md-3">
         <p class="text-uppercase text-success"><?php echo _l('booking_overview_expenses_billed'); ?></p>
         <p class="bold font-medium"><?php echo app_format_money(sum_from_table(db_prefix().'expenses',array('where'=>array('booking_id'=>$booking->id,'invoiceid !='=>'NULL','billable'=>1),'field'=>'amount')), $currency); ?></p>
      </div>
      <div class="col-md-3">
         <p class="text-uppercase text-danger"><?php echo _l('booking_overview_expenses_unbilled'); ?></p>
         <p class="bold font-medium"><?php echo app_format_money(sum_from_table(db_prefix().'expenses',array('where'=>array('booking_id'=>$booking->id,'invoiceid IS NULL','billable'=>1),'field'=>'amount')), $currency); ?></p>
      </div>
   </div>
</div>
<?php } ?>
<div class="booking-overview-timesheets-chart">
   <hr class="hr-panel-heading" />
   <div class="dropdown pull-right">
      <a href="#" class="dropdown-toggle" type="button" id="dropdownMenuBookingLoggedTime" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
         <?php if(!$this->input->get('overview_chart')){
            echo _l('this_week');
         } else {
            echo _l($this->input->get('overview_chart'));
         }
         ?>
         <span class="caret"></span>
      </a>
      <ul class="dropdown-menu" aria-labelledby="dropdownMenuBookingLoggedTime">
         <li><a href="<?php echo admin_url('booking/bookings/view/'.$booking->id.'?group=booking_overview&overview_chart=this_week'); ?>"><?php echo _l('this_week'); ?></a></li>
         <li><a href="<?php echo admin_url('booking/bookings/view/'.$booking->id.'?group=booking_overview&overview_chart=last_week'); ?>"><?php echo _l('last_week'); ?></a></li>
         <li><a href="<?php echo admin_url('booking/bookings/view/'.$booking->id.'?group=booking_overview&overview_chart=this_month'); ?>"><?php echo _l('this_month'); ?></a></li>
         <li><a href="<?php echo admin_url('booking/bookings/view/'.$booking->id.'?group=booking_overview&overview_chart=last_month'); ?>"><?php echo _l('last_month'); ?></a></li>
      </ul>
   </div>
   <div class="clearfix"></div>
   <canvas id="timesheetsChart" style="max-height:300px;" width="300" height="300"></canvas>
</div>

</div>
</div>
<div class="modal fade" id="add-edit-members" tabindex="-1" role="dialog">
   <div class="modal-dialog">
      <?php echo form_open(admin_url('booking/bookings/add_edit_members/'.$booking->id)); ?>
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo _l('booking_members'); ?></h4>
         </div>
         <div class="modal-body">
            <?php
            $selected = array();
            foreach($members as $member){
              array_push($selected,$member['staff_id']);
           }
           echo render_select('booking_members[]',$staff,array('staffid',array('firstname','lastname')),'booking_members',$selected,array('multiple'=>true,'data-actions-box'=>true),array(),'','',false);
           ?>
        </div>
        <div class="modal-footer">
         <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
         <button type="submit" class="btn btn-info" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('submit'); ?></button>
      </div>
   </div>
   <!-- /.modal-content -->
   <?php echo form_close(); ?>
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php if(isset($booking_overview_chart)){ ?>
<script>
   var booking_overview_chart = <?php echo json_encode($booking_overview_chart); ?>;
</script>
<?php } ?>
