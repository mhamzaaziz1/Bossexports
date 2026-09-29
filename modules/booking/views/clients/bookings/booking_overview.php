<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row mtop15">
 <div class="col-md-6">
  <div class="panel-heading booking-info-bg no-radius"><?php echo _l('booking_overview'); ?></div>
  <div class="panel-body no-radius">
   <table class="table table-borded no-margin">
    <tbody>
    <?php if($booking->billing_type == 1 || $booking->billing_type == 2){
     echo '<tr class="booking-cost">';
     if($booking->billing_type == 1){
       echo '<td class="bold">'._l('booking_total_cost').'</td>';
       echo '<td>'.app_format_money($booking->booking_cost, $currency).'</td>';
     } else {
       echo '<td class="bold">'._l('booking_rate_per_hour').'</td>';
       echo '<td>'.app_format_money($booking->booking_rate_per_hour, $currency).'</td>';
     }
     echo '<tr>';
   }
   ?>
   <tr>
    <td class="bold"><?php echo _l('booking_status'); ?></td>
    <td><?php echo $booking_status['name']; ?></td>
  </tr>
  <tr>
    <td class="bold"><?php echo _l('booking_start_date'); ?></td>
    <td><?php echo _d($booking->start_date); ?></td>
  </tr>
  <?php if($booking->deadline){ ?>
    <tr>
      <td class="bold"><?php echo _l('booking_deadline'); ?></td>
      <td><?php echo _d($booking->deadline); ?></td>
    </tr>
  <?php } ?>
  <?php if($booking->date_finished){ ?>
    <tr class="text-success">
      <td class="bold"><?php echo _l('booking_completed_date'); ?></td>
      <td><?php echo _d($booking->date_finished); ?></td>
    </tr>
  <?php } ?>
  <?php $custom_fields = get_custom_fields('bookings',array('show_on_client_portal'=>1));
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
</div>
<div class="col-md-6 text-center">
 <p class="bold"><?php echo _l('booking_progress_text'); ?></p>
 <div class="booking-progress relative mtop30" data-thickness="22" data-reverse="true" data-size="170" data-value="<?php echo $percent; ?>">
   <strong class="booking-percent"></strong>
 </div>
</div>
<div class="clearfix"></div>
<div class="col-md-12">
  <hr />
</div>
  <div class="col-md-<?php echo ($booking->deadline ? 6 : 12); ?> booking-progress-bars">
    <div class="row">
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
<?php if($booking->deadline){ ?>
  <div class="col-md-6 booking-progress-bars">
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

  <div class="col-md-12 booking-overview-column">
    <div class="row">
     <div class="col-md-12">
       <hr />
       <?php
       if($booking->billing_type == 3 || $booking->billing_type == 2){ ?>

         <div class="row">
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
        </div>
        <hr />
      <?php } ?>
    </div>
  </div>
    <div class="row">
     <div class="col-md-3">
      <p class="text-uppercase text-muted"><?php echo _l('booking_overview_expenses'); ?></span></p>
      <p class="bold font-medium"><?php echo app_format_money(sum_from_table(db_prefix().'expenses',array('where'=>array('booking_id'=>$booking->id),'field'=>'amount')), $currency); ?></p>
    </div>
    <div class="col-md-3">
      <p class="text-uppercase text-info"><?php echo _l('booking_overview_expenses_billable'); ?></span></p>
      <p class="bold font-medium"><?php echo app_format_money(sum_from_table(db_prefix().'expenses',array('where'=>array('booking_id'=>$booking->id,'billable'=>1),'field'=>'amount')), $currency); ?></p>
    </div>
    <div class="col-md-3">
      <p class="text-uppercase text-success"><?php echo _l('booking_overview_expenses_billed'); ?></span></p>
      <p class="bold font-medium"><?php echo app_format_money(sum_from_table(db_prefix().'expenses',array('where'=>array('booking_id'=>$booking->id,'invoiceid !='=>'NULL','billable'=>1),'field'=>'amount')), $currency); ?></p>
    </div>
    <div class="col-md-3">
      <p class="text-uppercase text-danger"><?php echo _l('booking_overview_expenses_unbilled'); ?></span></p>
      <p class="bold font-medium"><?php echo app_format_money(sum_from_table(db_prefix().'expenses',array('where'=>array('booking_id'=>$booking->id,'invoiceid IS NULL','billable'=>1),'field'=>'amount')), $currency); ?></p>
    </div>
  </div>
</div>

<div class="col-md-12">
  <hr />
</div>
<div class="clearfix"></div>
<div class="col-md-6">
  <div class="panel-heading booking-info-bg no-radius"><?php echo _l('booking_description'); ?></div>
  <div class="panel-body no-radius tc-content booking-description">
    <?php if(empty($booking->description)){
     echo '<p class="text-muted text-center no-mbot">' . _l('no_description_booking') . '</p>';
   }
   echo check_for_links($booking->description); ?>
 </div>
</div>
  <div class="col-md-6 team-members booking-overview-column">
    <div class="panel-heading booking-info-bg no-radius"><?php echo _l('booking_members'); ?></div>
    <div class="panel-body">
     <?php
     if(count($members) == 0){
      echo '<div class="media-body text-center text-muted"><p>'._l('no_booking_members').'</p></div>';
    }
    foreach($members as $member){ ?>
      <div class="media">
        <div class="media-left">
         <?php echo staff_profile_image($member['staff_id'],array('staff-profile-image-small','media-object')); ?>
       </div>
       <div class="media-body">
         <h5 class="media-heading mtop5"><?php echo get_staff_full_name($member['staff_id']); ?></h5>
       </div>
     </div>
   <?php } ?>
 </div>
</div>
</div>
