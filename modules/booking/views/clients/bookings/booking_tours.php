<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if($booking->settings->view_tours == 1){ ?>
<!-- Booking Tours -->
<?php if(!isset($view_tour)) { ?>
<div class="pull-right tours-table<?php if($booking->settings->view_milestones == 1){echo ' hide';} ?>">
   <select class="selectpicker" name="toursFilterByStatus[]" onchange="dt_custom_view('.table-tours', 3, $(this).selectpicker('val'));" multiple="true" data-none-selected-text="<?php echo _l('filter_by'); ?>">
      <?php foreach($tours_statuses as $status) { ?>
      <option value="<?php echo $status['name']; ?>"><?php echo $status['name']; ?></option>
      <?php } ?>
   </select>
</div>
<?php } ?>
<?php if($booking->settings->view_milestones == 1 && !isset($view_tour)){ ?>
<a href="#" class="btn btn-default pull-left tour-table-toggle" onclick="tourTable(); return false;">
    <i class="fa fa-th-list"></i>
</a>
<div class="clearfix"></div>
<hr />
<div class="tours-phases">
   <?php
      $milestones = array();
      $milestones[] = array(
       'name'=>_l('milestones_uncategorized'),
       'id'=>0,
       'total_logged_time'=>$this->bookings_model->calc_milestone_logged_time($booking->id,0),
       'total_tours'=>total_booking_tours_by_milestone(0, $booking->id),
       'total_finished_tours'=>total_booking_finished_tours_by_milestone(0, $booking->id),
       'color'=>NULL,
      );
      $_milestones = $this->bookings_model->get_milestones($booking->id);
      foreach($_milestones as $m){
          $milestones[] = $m;
      }
      ?>
   <div class="kan-ban-row">
      <?php foreach($milestones as $milestone){
         $tours = $this->bookings_model->get_tours($booking->id,array('milestone'=>$milestone['id']));
         $percent              = 0;
         if ($milestone['total_finished_tours'] >= floatval($milestone['total_tours'])) {
          $percent = 100;
         } else {
           if ($milestone['total_tours'] !== 0) {
              $percent = number_format(($milestone['total_finished_tours'] * 100) / $milestone['total_tours'], 2);
           }
         }
         $milestone_color = '';
         if(!empty($milestone["color"]) && !is_null($milestone['color'])){
            $milestone_color = ' style="background:'.$milestone["color"].';border:1px solid '.$milestone['color'].'"';
         }
         ?>
      <div class="kan-ban-col<?php if($milestone['id'] == 0 && count($tours) == 0){echo ' hide'; } ?>">
         <div class="panel-heading <?php if($milestone_color != ''){echo 'color-not-auto-adjusted color-white ';} ?><?php if($milestone['id'] != 0){echo 'tour-phase';}else{echo 'info-bg';} ?>"<?php echo $milestone_color; ?>>
            <?php if($milestone['id'] != 0 && $milestone['description_visible_to_customer'] == 1){ ?>
            <i class="fa fa-file-text pointer" aria-hidden="true" data-toggle="popover" data-title="<?php echo _l('milestone_description'); ?>" data-html="true" data-content="<?php echo htmlspecialchars($milestone['description']); ?>"></i>&nbsp;
            <?php } ?>
            <span class="bold"><?php echo $milestone['name']; ?></span>
            <?php if($booking->settings->view_tour_total_logged_time == 1){ ?>
            <?php echo '<br /><small>' . _l('milestone_total_logged_time') . ': ' . seconds_to_time_format($milestone['total_logged_time']). '</small>';
               } ?>
         </div>
         <div class="panel-body">
            <?php
               if(count($tours) == 0){
                echo _l('milestone_no_tours_found');
               }
               foreach($tours as $tour){ ?>
            <div class="media _tour_wrapper<?php if((!empty($tour['duedate']) && $tour['duedate'] < date('Y-m-d')) && $tour['status'] != Tours_model::STATUS_COMPLETE){ echo ' overdue-tour'; } ?>">
               <div class="media-body">
                  <a href="<?php echo site_url('clients/booking/'.$booking->id.'?group=booking_tours&tourid='.$tour['id']); ?>" class="tour_milestone pull-left<?php if($tour['status'] == Tours_model::STATUS_COMPLETE){echo ' line-throught text-muted';} ?>"><?php echo $tour['name']; ?></a>
                  <?php if(
                     $booking->settings->edit_tours == 1 &&
                     $tour['is_added_from_contact'] == 1 &&
                     $tour['addedfrom'] == get_contact_user_id() &&
                     $tour['billed'] == 0
                     ){ ?>
                  <a href="<?php echo site_url('clients/booking/'.$booking->id.'?group=edit_tour&tourid='.$tour['id']); ?>" class="pull-right">
                  <small><i class="fa-regular fa-pen-to-square"></i></small>
                  </a>
                  <?php } ?>
                  <br />
                  <small><?php echo _l('tour_status'); ?>: <?php echo format_tour_status($tour['status'],true); ?></small>
                  <br />
                  <small><?php echo _l('tours_dt_datestart'); ?>: <b><?php echo _d($tour['startdate']); ?></b></small>
                  <?php if(is_date($tour['duedate'])){ ?>
                  -
                  <small><?php echo _l('tour_duedate'); ?>: <b><?php echo _d($tour['duedate']); ?></b></small>
                  <?php } ?>
               </div>
            </div>
            <?php } ?>
         </div>
         <div class="panel-footer">
            <div class="progress no-margin progress-bg-dark">
               <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $percent; ?>">
               </div>
            </div>
         </div>
      </div>
      <?php } ?>
   </div>
</div>
<?php } ?>
<?php if(!isset($view_tour)){ ?>
<div class="clearfix"></div>
<hr class="tours-table" />
<div class="tours-table<?php if($booking->settings->view_milestones == 1){echo ' hide';} ?>">
   <table class="table dt-table table-tours" data-order-col="3" data-s-type='[{"column":3,"type":"tour-status"}]' data-order-type="asc">
      <thead>
         <tr>
            <th><?php echo _l('tours_dt_name'); ?></th>
            <th><?php echo _l('tours_dt_datestart'); ?></th>
            <th><?php echo _l('tour_duedate'); ?></th>
            <th><?php echo _l('tour_status'); ?></th>
            <?php if($booking->settings->view_milestones == 1){ ?>
            <th><?php echo _l('tour_milestone'); ?></th>
            <?php } ?>
            <th><?php echo _l('tour_billable'); ?></th>
            <th><?php echo _l('tour_billed'); ?></th>
            <?php
               $custom_fields = get_custom_fields('tours',array('show_on_client_portal'=>1));
               foreach($custom_fields as $field){ ?>
            <th><?php echo $field['name']; ?></th>
            <?php } ?>
         </tr>
      </thead>
      <tbody>
         <?php
            foreach($booking_tours as $tour){ ?>
         <tr>
            <td>
               <?php if(
                  $booking->settings->edit_tours == 1 &&
                  $tour['is_added_from_contact'] == 1 &&
                  $tour['addedfrom'] == get_contact_user_id() &&
                  $tour['billed'] == 0
                  ){ ?>
               <a href="<?php echo site_url('clients/booking/'.$booking->id.'?group=edit_tour&tourid='.$tour['id']); ?>"><i class="fa-regular fa-pen-to-square"></i></a>
               <?php } ?>
               <a href="<?php echo site_url('clients/booking/'.$booking->id.'?group=booking_tours&tourid='.$tour['id']); ?>">
               <?php echo $tour['name']; ?></a>
            </td>
            <td data-order="<?php echo $tour['startdate']; ?>"><?php echo _d($tour['startdate']); ?></td>
            <td data-order="<?php echo $tour['duedate']; ?>"><?php echo _d($tour['duedate']); ?></td>
            <td data-order="<?php echo $tour['status']; ?>">
               <?php echo format_tour_status($tour['status']); ?>
            </td>
            <?php if($booking->settings->view_milestones == 1){ ?>
            <td data-order="<?php echo $tour['milestone_name']; ?>"><?php if($tour['milestone'] != 0){ echo $tour['milestone_name']; } ?></td>
            <?php } ?>
            <td data-order="<?php echo $tour['billable']; ?>">
               <?php
                  if($tour['billable'] == 1){
                   $billable = _l("tour_billable_yes");
                  } else {
                   $billable = _l("tour_billable_no");
                  }
                  echo $billable;
                  ?>
            </td>
            <td data-order="<?php echo $tour['billed']; ?>">
               <?php
                  if($tour['billed'] == 1){
                   $billed = '<span class="label label-success pull-left">'._l('tour_billed_yes').'</span>';
                  } else {
                   $billed = '<span class="label label-'.($tour['billable'] == 1 ? 'danger' : 'default').' pull-left">'._l('tour_billed_no').'</span>';
                  }
                  echo $billed;
                  ?>
            </td>
            <?php foreach($custom_fields as $field){ ?>
            <td><?php echo get_custom_field_value($tour['id'],$field['id'],'tours'); ?></td>
            <?php } ?>
         </tr>
         <?php } ?>
      </tbody>
   </table>
</div>
<?php } else {
   get_template_part('bookings/booking_tour');
   }
}
?>
<script>
   $(function(){
      var milesonesColumns = $('.tours-phases .kan-ban-col:visible');
      var totalMilestones = milesonesColumns.length;
      if(totalMilestones > 0){
          var phaseWidth = milesonesColumns.eq(0).width();
          $('.kan-ban-row').css('min-width', totalMilestones * (phaseWidth + 20) + 'px');
      } else if($('.tour-table-toggle').length > 0) {
        // When there are no milestones and the client is allowed to view milestones, show the tours table as default
        tourTable();
      }
   });
</script>
