<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="customer-profile-group-heading"><?php echo _l('bookings'); ?></h4>
<?php if(isset($client)){ ?>
<?php if(has_permission('bookings','','create')){ ?>
<a href="<?php echo admin_url('booking/bookings/booking?customer_id='.$client->userid); ?>" class="btn btn-info mbot25<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('new_booking'); ?></a>
<?php } ?>
<div class="row">
   <?php
      $_where = '';
      if(!has_permission('bookings','','view')){
        $_where = 'id IN (SELECT booking_id FROM '.db_prefix().'booking_members WHERE staff_id='.get_staff_user_id().')';
      }
      ?>
   <?php foreach($booking_statuses as $status){ ?>
   <div class="col-md-5ths total-column">
      <div class="panel_s">
         <div class="panel-body">
            <h3 class="text-muted _total">
               <?php $where = ($_where == '' ? '' : $_where.' AND ').'status = '.$status['id']. ' AND clientid='.$client->userid; ?>
               <?php echo total_rows(db_prefix().'bookings',$where); ?>
            </h3>
            <span style="color:<?php echo $status['color']; ?>"><?php echo $status['name']; ?></span>
         </div>
      </div>
   </div>
   <?php } ?>
</div>
<?php
   $this->load->view('admin/bookings/table_html', array('class'=>'bookings-single-client'));
}
?>
