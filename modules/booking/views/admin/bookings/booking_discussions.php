<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if(!isset($discussion)){ ?>
<a href="#" onclick="new_discussion();return false;" class="btn btn-info mbot25"><?php echo _l('new_booking_discussion'); ?></a>
<?php
    $this->load->view('admin/bookings/booking_discussion');
    render_datatable(array(
     _l('booking_discussion_subject'),
     _l('booking_discussion_last_activity'),
     _l('booking_discussion_total_comments'),
     _l('booking_discussion_show_to_customer'),
     ),'booking-discussions'); ?>
<?php } else { ?>
<h3 class="bold no-margin"><?php echo $discussion->subject; ?></h3>
<hr />
<p class="no-margin"><?php echo _l('booking_discussion_posted_on',_d($discussion->datecreated)); ?></p>
<p class="no-margin">
    <?php if($discussion->staff_id == 0){
        echo _l('booking_discussion_posted_by',get_contact_full_name($discussion->contact_id)) . ' <span class="label label-info inline-block">'._l('is_customer_indicator').'</span>';
        } else {
        echo _l('booking_discussion_posted_by',get_staff_full_name($discussion->staff_id));
        }
        ?>
</p>
<p><?php echo _l('booking_discussion_total_comments'); ?>: <?php echo total_rows(db_prefix().'bookingdiscussioncomments',array('discussion_id'=>$discussion->id)); ?>
<p class="text-muted"><?php echo $discussion->description; ?></p>
<hr />
<div id="discussion-comments" class="tc-content"></div>
<?php } ?>
