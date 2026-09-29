   <?php
   $pinned_bookings = get_user_pinned_bookings();
   if(count($pinned_bookings) > 0){ ?>
      <li class="pinned-separator"></li>
      <?php foreach($pinned_bookings as $booking_pin){ ?>
         <li class="pinned_booking">
            <a href="<?php echo admin_url('booking/bookings/view/'.$booking_pin['id']); ?>" data-toggle="tooltip" data-title="<?php echo _l('pinned_booking'); ?>"><?php echo $booking_pin['name']; ?><br><small><?php echo $booking_pin["company"]; ?></small></a>
            <div class="col-md-12">
               <div class="progress progress-bar-mini">
                  <div class="progress-bar no-percent-text not-dynamic" role="progressbar" data-percent="<?php echo $booking_pin['progress']; ?>" style="width: <?php echo $booking_pin['progress']; ?>%;">
                  </div>
               </div>
            </div>
         </li>
      <?php } ?>
      <?php } ?>
