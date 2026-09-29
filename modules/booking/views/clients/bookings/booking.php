<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('booking_id',$booking->id); ?>
<div class="panel_s">
    <div class="panel-body">
       <h3 class="bold mtop10 booking-name pull-left"><?php echo $booking->name; ?>
            <span style="color:<?php echo $booking_status['color']; ?>; font-size:16px;"><?php echo $booking_status['name']; ?></span>
       </h3>
        <?php if($booking->settings->view_tours == 1 && $booking->settings->create_tours == 1){ ?>
        <a href="<?php echo site_url('booking/bookings_public/booking/'.$booking->id.'?group=new_tour'); ?>" class="btn btn-info pull-right mtop5"><?php echo _l('new_tour'); ?></a>
        <?php } ?>
   </div>
</div>
<div class="panel_s">
    <div class="panel-body">
        <?php $this->view('clients/bookings/booking_tabs'); ?>
        <div class="clearfix mtop15"></div>
        <?php $this->view('clients/bookings/'.$group); ?>
    </div>
</div>
