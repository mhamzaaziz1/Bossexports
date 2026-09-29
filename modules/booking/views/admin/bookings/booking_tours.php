<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- Booking Tours -->
<?php
    if($booking->settings->hide_tours_on_main_tours_table == '1') {
        echo '<i class="fa fa-exclamation fa-2x pull-left" data-toggle="tooltip" data-title="'._l('booking_hide_tours_settings_info').'"></i>';
    }
?>
<div class="tours-table">
    <?php init_relation_tours_table(array( 'data-new-rel-id'=>$booking->id,'data-new-rel-type'=>'booking')); ?>
</div>
