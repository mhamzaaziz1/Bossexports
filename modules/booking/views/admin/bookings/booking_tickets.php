<?php defined('BASEPATH') or exit('No direct script access allowed');
    $this->load->view('admin/tickets/summary',array('booking_id'=>$booking->id));
    echo form_hidden('booking_id',$booking->id);
    echo '<div class="clearfix"></div>';
    if(((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member())){
        echo '<a href="'.admin_url('tickets/add?booking_id='.$booking->id).'" class="mbot20 btn btn-info">'._l('new_ticket').'</a>';
    }
    echo AdminTicketsTableStructure('tickets-table');
?>
