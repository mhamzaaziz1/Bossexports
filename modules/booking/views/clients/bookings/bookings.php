<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s section-heading section-bookings">
   <div class="panel-body">
      <h4 class="no-margin section-text"><?php echo _l('clients_my_bookings'); ?></h4>
   </div>
</div>
<div class="panel_s">
   <div class="panel-body">
      <div class="row mbot15">
         <div class="col-md-12">
            <h3 class="text-success bookings-summary-heading no-mtop mbot15"><?php echo _l('bookings_summary'); ?></h3>
         </div>
         <?php $this->view('clients/bookings/booking_summary'); ?>
      </div>
      <hr />
         <table class="table dt-table table-bookings" data-order-col="2" data-order-type="desc">
            <thead>
               <tr>
                  <th class="th-booking-name"><?php echo _l('booking_name'); ?></th>
                  <th class="th-booking-start-date"><?php echo _l('booking_start_date'); ?></th>
                  <th class="th-booking-deadline"><?php echo _l('booking_deadline'); ?></th>
                  <th class="th-booking-billing-type"><?php echo _l('booking_billing_type'); ?></th>
                  <?php
                     $custom_fields = get_custom_fields('bookings',array('show_on_client_portal'=>1));
                     foreach($custom_fields as $field){ ?>
                  <th><?php echo $field['name']; ?></th>
                  <?php } ?>
                  <th><?php echo _l('booking_status'); ?></th>
               </tr>
            </thead>
            <tbody>
               <?php foreach($bookings as $booking){ ?>
               <tr>
                  <td><a href="<?php echo site_url('booking/bookings_public/booking/'.$booking['id']); ?>"><?php echo $booking['name']; ?></a></td>
                  <td data-order="<?php echo $booking['start_date']; ?>"><?php echo _d($booking['start_date']); ?></td>
                  <td data-order="<?php echo $booking['deadline']; ?>"><?php echo _d($booking['deadline']); ?></td>
                  <td>
                     <?php
                        if($booking['billing_type'] == 1){
                          $type_name = 'booking_billing_type_fixed_cost';
                        } else if($booking['billing_type'] == 2){
                          $type_name = 'booking_billing_type_booking_hours';
                        } else {
                          $type_name = 'booking_billing_type_booking_tour_hours';
                        }
                        echo _l($type_name);
                        ?>
                  </td>
                  <?php foreach($custom_fields as $field){ ?>
                  <td><?php echo get_custom_field_value($booking['id'],$field['id'],'bookings'); ?></td>
                  <?php } ?>
                  <td>
                     <?php
                        $status = get_booking_status_by_id($booking['status']);
                        echo '<span class="label inline-block" style="color:'.$status['color'].';border:1px solid '.$status['color'].'">'.$status['name'].'</span>';
                        ?>
                  </td>
               </tr>
               <?php } ?>
            </tbody>
         </table>
   </div>
</div>
