<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="mark_tours_finished_modal" tabindex="-1" role="dialog" data-toggle="modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4><?php echo _l('additional_action_required'); ?></h4>
        </div>
        <div class="modal-body">
          <?php if(booking_has_recurring_tours($booking->id)) { ?>
            <div class="alert alert-warning recurring-tours-notice hide" data-notice-text="<?php echo _l('booking_changing_status_recurring_tours_notice'); ?>">
            </div>
        <?php } ?>
          <div class="checkbox checkbox-primary">
            <input type="checkbox" name="notify_booking_members_status_change" id="notify_booking_members_status_change">
            <label for="notify_booking_members_status_change"><?php echo _l('notify_booking_members_status_change'); ?></label>
        </div>
        <div class="checkbox checkbox-primary">
            <input type="checkbox" name="mark_all_tours_as_completed" id="mark_all_tours_as_completed">
            <label for="mark_all_tours_as_completed"><?php echo _l('booking_mark_all_tours_as_completed'); ?></label>
        </div>
        <?php if(is_email_template_active('booking-finished-to-customer') && total_rows(db_prefix().'contacts',array('userid'=>$booking->clientid,'active'=>1)) > 0){ ?>
        <div class="form-group booking_marked_as_finished hide no-mbot">
            <hr />
            <div class="checkbox checkbox-primary">
                <input type="checkbox" name="booking_marked_as_finished_email_to_contacts" id="booking_marked_as_finished_email_to_contacts">
                <label for="booking_marked_as_finished_email_to_contacts"><?php echo _l('booking_marked_as_finished_to_contacts'); ?></label>
            </div>
        </div>
        <?php } ?>
    </div>
    <div class="modal-footer">
        <button class="btn btn-info" id="booking_mark_status_confirm" onclick="confirm_booking_status_change(this); return false;"><?php echo _l('booking_mark_tours_finished_confirm'); ?></button>
    </div>
</div>
</div>
</div>
