<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<li data-tour-id="<?php echo $tour['id']; ?>" class="tour<?php if(has_permission('tours','','create') || has_permission('tours','','edit')){echo ' sortable';} ?><?php if($tour['current_user_is_assigned']){echo ' current-user-tour';} if((!empty($tour['duedate']) && $tour['duedate'] < date('Y-m-d')) && $tour['status'] != Tours_model::STATUS_COMPLETE){ echo ' overdue-tour'; } ?>">
  <div class="panel-body">
    <div class="media">
      <?php
      $assignees = explode(',',$tour['assignees_ids']);
      if(count($assignees) > 0 && $assignees[0] != ''){ ?>
      <div class="media-left">
        <?php if($tour['current_user_is_assigned']){
         echo staff_profile_image(get_staff_user_id(),array('staff-profile-image-small pull-left'),'small',array('data-toggle'=>'tooltip','data-title'=>_l('booking_tour_assigned_to_user')));
       }
       foreach($assignees as $assigned){
        $assigned = trim($assigned);
        if($assigned != get_staff_user_id()){
         echo staff_profile_image($assigned,array('staff-profile-image-xs sub-staff-assigned-milestone pull-left'),'small',array('data-toggle'=>'tooltip','data-title'=>get_staff_full_name($assigned)));
       }
     }

     ?>
   </div>
   <?php } ?>
   <div class="media-body">
    <a href="<?php echo admin_url('tours/view/'.$tour['id']); ?>" class="tour_milestone pull-left mbot5 mtop5<?php if($tour['status'] == Tours_model::STATUS_COMPLETE){echo ' text-muted line-throught';} ?>" onclick="init_tour_modal(<?php echo $tour['id']; ?>); return false;"><?php echo $tour['name']; ?></a>
    <div class="clearfix"></div>
    <?php if(has_permission('tours','','create')){ ?>
    <small>
      <?php echo _l('tour_total_logged_time'); ?>
      <b>
        <?php echo seconds_to_time_format($tour['total_logged_time']); ?>
      </b>
    </small> -
    <?php } ?>
    <small><?php echo _l('tour_status'); ?>: <?php echo format_tour_status($tour['status'],true); ?></small>
    <br />
    <small><?php echo _l('tours_dt_datestart'); ?>: <b><?php echo _d($tour['startdate']); ?></b></small>
    <?php if(is_date($tour['duedate'])){ ?>
    -
    <small><?php echo _l('tour_duedate'); ?>: <b><?php echo _d($tour['duedate']); ?></b></small>
    <?php } ?>
  </div>
</div>
</div>
</li>
