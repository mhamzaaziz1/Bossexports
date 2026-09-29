<?php defined('BASEPATH') or exit('No direct script access allowed');

$where = array();
if($this->input->get('booking_id')){
  $where['rel_id'] = $this->input->get('booking_id');
  $where['rel_type'] = 'booking';
}
foreach ($tour_statuses as $status) {
  $total_pages = ceil($this->tours_model->do_kanban_query($status['id'],$this->input->get('search'),1,true,$where)/get_option('tours_kanban_limit'));
  ?>
  <ul class="kan-ban-col tours-kanban" data-col-status-id="<?php echo $status['id']; ?>" data-total-pages="<?php echo $total_pages; ?>">
    <li class="kan-ban-col-wrapper">
      <div class="border-right panel_s">
        <div class="panel-heading-bg" style="background:<?php echo $status['color']; ?>;border-color:<?php echo $status['color']; ?>;color:#fff; ?>" data-status-id="<?php echo $status['id']; ?>">
          <div class="kan-ban-step-indicator<?php if($status['id'] == Tours_model::STATUS_COMPLETE){ echo ' kan-ban-step-indicator-full'; } ?>"></div>
          <span class="heading"><?php echo format_tour_status($status['id'],false,true); ?>
          </span>
          <a href="#" onclick="return false;" class="pull-right color-white">
          </a>
        </div>
        <div class="kan-ban-content-wrapper">
          <div class="kan-ban-content">
            <ul class="status tours-status sortable relative" data-tour-status-id="<?php echo $status['id']; ?>">
              <?php
              $tours = $this->tours_model->do_kanban_query($status['id'],$this->input->get('search'),1,false,$where);
              $total_tours = count($tours);
              foreach ($tours as $tour) {
                if ($tour['status'] == $status['id']) {
                  $this->load->view('admin/tours/_kan_ban_card',array('tour'=>$tour,'status'=>$status['id']));
                } } ?>
                <?php if($total_tours > 0 ){ ?>
                <li class="text-center not-sortable kanban-load-more" data-load-status="<?php echo $status['id']; ?>">
                 <a href="#" class="btn btn-default btn-block<?php if($total_pages <= 1){echo ' disabled';} ?>" data-page="1" onclick="kanban_load_more(<?php echo $status['id']; ?>,this,'tours/tours_kanban_load_more',265,360); return false;";><?php echo _l('load_more'); ?></a>
               </li>
               <?php } ?>
               <li class="text-center not-sortable mtop30 kanban-empty<?php if($total_tours > 0){echo ' hide';} ?>">
                <h4>
                  <i class="fa fa-circle-o-notch" aria-hidden="true"></i><br /><br />
                  <?php echo _l('no_tours_found'); ?></h4>
                </li>
              </ul>
            </div>
          </div>
        </li>
      </ul>
      <?php } ?>
