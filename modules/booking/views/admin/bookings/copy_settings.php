<?php
    defined('BASEPATH') or exit('No direct script access allowed');
    $tour_statuses = $this->tours_model->get_statuses();
?>
<!-- Copy Booking -->
<div class="modal fade" id="copy_booking" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('booking/bookings/copy/'.(isset($booking) ? $booking->id : '')),array('id'=>'copy_form','data-copy-url'=>admin_url('booking/bookings/copy/'))); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <?php echo _l('copy_booking'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" class="copy" name="tours" id="c_tours" checked>
                            <label for="c_tours"><?php echo _l('tours'); ?></label>
                        </div>
                        <div class="checkbox checkbox-primary mleft10 tours-copy-option">
                            <input type="checkbox" name="tours_include_checklist_items" id="tours_include_checklist_items" checked>
                            <label for="tours_include_checklist_items"><small><?php echo _l('copy_booking_tour_include_check_list_items'); ?></small></label>
                        </div>
                        <div class="checkbox checkbox-primary mleft10 tours-copy-option">
                            <input type="checkbox" name="tour_include_assignees" id="tour_include_assignees" checked>
                            <label for="tour_include_assignees"><small><?php echo _l('copy_booking_tour_include_assignees'); ?></small></label>
                        </div>
                        <div class="checkbox checkbox-primary mleft10 tours-copy-option">
                            <input type="checkbox" name="tour_include_followers" id="copy_booking_tour_include_followers" checked>
                            <label for="copy_booking_tour_include_followers"><small><?php echo _l('copy_booking_tour_include_followers'); ?></small></label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="milestones" id="c_milestones" checked>
                            <label for="c_milestones"><?php echo _l('booking_milestones'); ?></label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="members" id="c_members" class="copy" checked>
                            <label for="c_members"><?php echo _l('booking_members'); ?></label>
                        </div>
                        <hr />
                        <div class="copy-booking-tours-status-wrapper">
                            <p class="bold"><?php echo _l('copy_booking_tours_status'); ?></p>
                            <?php foreach($tour_statuses as $cp_tour_status){ ?>
                                <div class="radio radio-primary">
                                    <input type="radio" name="copy_booking_tour_status" value="<?php echo $cp_tour_status['id']; ?>" id="cp_tour_status_<?php echo $cp_tour_status['id']; ?>"<?php if($cp_tour_status['id'] == '1'){echo ' checked';} ?>>
                                    <label for="cp_tour_status_<?php echo $cp_tour_status['id']; ?>"><?php echo $cp_tour_status['name']; ?></label>
                                </div>
                            <?php } ?>
                            <hr />
                        </div>
                        <div class="form-group">
                          <label for="clientid_copy_booking"><?php echo _l('booking_customer'); ?></label>
                          <select id="clientid_copy_booking" name="clientid_copy_booking" data-live-search="true" data-width="100%" class="ajax-search" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                        </select>
                      </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_date_input('start_date','booking_start_date',_d(date('Y-m-d'))); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo render_date_input('deadline','booking_deadline'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" data-form="#copy_form" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>"  class="btn btn-info"><?php echo _l('copy_booking'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!-- Copy Booking end -->
<script>
// Copy booking modal and set url if ID is passed manually eq from booking list area
function copy_booking(id) {

    $('#copy_booking').modal('show');

    if (typeof(id) != 'undefined') {
        $('#copy_form').attr('action', $('#copy_form').data('copy-url') + id);
    }

    appValidateForm($('#copy_form'), {
        start_date: 'required',
        clientid_copy_booking: 'required',
    });

    var copy_members = $('#c_members');
    var copy_tours = $('input[name="tours"].copy');
    var copy_assignees_and_followers = $('input[name="tour_include_assignees"],input[name="tour_include_followers"]');

    copy_members.off('change');
    copy_tours.off('change');
    copy_assignees_and_followers.off('change');

        copy_members.on('change',function(){
            if(!$(this).prop('checked')) {
                copy_assignees_and_followers.prop('checked',false)
           }
       });

        copy_tours.on('change', function() {
          var checked = $(this).prop('checked');
          if (checked) {

              var copy_assignees = $('input[name="tour_include_assignees"]').prop('checked');
              var copy_followers = $('input[name="tour_include_followers"]').prop('checked');

              if (copy_assignees || copy_followers) {
                  $('input[name="members"].copy').prop('checked', true);
              }

              $('.copy-booking-tours-status-wrapper').removeClass('hide');
              $('.tours-copy-option').removeClass('hide');

          } else {
              $('.copy-booking-tours-status-wrapper').addClass('hide');
              $('.tours-copy-option').addClass('hide');
          }
      });

      copy_assignees_and_followers.on('change', function() {
          var checked = $(this).prop('checked');
          if (checked == true) {
              $('input[name="members"].copy').prop('checked', true);
          }
      });
}

</script>
