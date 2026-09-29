<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                <div class="row _buttons">
                     <div class="col-md-8">
                        <?php if(has_permission('tours','','create')){ ?>
                        <a href="#" onclick="new_tour(<?php if($this->input->get('booking_id')){ echo "'".admin_url('tours/tour?rel_id='.$this->input->get('booking_id').'&rel_type=booking')."'";} ?>); return false;" class="btn btn-info pull-left new"><?php echo _l('new_tour'); ?></a>
                        <?php } ?>
                        <a href="<?php if(!$this->input->get('booking_id')){ echo admin_url('tours/switch_kanban/'.$switch_kanban); } else { echo admin_url('booking/bookings/view/'.$this->input->get('booking_id').'?group=booking_tours'); }; ?>" class="btn btn-default mleft10 pull-left hidden-xs">
                           <?php if($switch_kanban == 1){ echo _l('switch_to_list_view');}else{echo _l('leads_switch_to_kanban');}; ?>
                        </a>
                     </div>
                     <div class="col-md-4">
                        <?php if($this->session->has_userdata('tours_kanban_view') && $this->session->userdata('tours_kanban_view') == 'true') { ?>
                        <div data-toggle="tooltip" data-placement="bottom" data-title="<?php echo _l('search_by_tags'); ?>">
                           <?php echo render_input('search','','','search',array('data-name'=>'search','onkeyup'=>'tours_kanban();','placeholder'=>_l('search_tours')),array(),'no-margin') ?>
                        </div>
                        <?php } else { ?>
                        <?php $this->load->view('admin/tours/tours_filter_by',array('view_table_name'=>'.table-tours')); ?>
                        <a href="<?php echo admin_url('tours/detailed_overview'); ?>" class="btn btn-success pull-right mright5"><?php echo _l('detailed_overview'); ?></a>
                        <?php } ?>
                     </div>
                  </div>
                  <hr class="hr-panel-heading hr-10" />
                  <div class="clearfix"></div>
                  <?php
                  if($this->session->has_userdata('tours_kanban_view') && $this->session->userdata('tours_kanban_view') == 'true') { ?>
                  <div class="kan-ban-tab" id="kan-ban-tab" style="overflow:auto;">
                     <div class="row">
                        <div id="kanban-params">
                           <?php echo form_hidden('booking_id',$this->input->get('booking_id')); ?>
                        </div>
                        <div class="container-fluid">
                           <div id="kan-ban"></div>
                        </div>
                     </div>
                  </div>
                  <?php } else { ?>
                  <?php $this->load->view('admin/tours/_summary',array('table'=>'.table-tours')); ?>
                  <a href="#" data-toggle="modal" data-target="#tours_bulk_actions" class="hide bulk-actions-btn table-btn" data-table=".table-tours"><?php echo _l('bulk_actions'); ?></a>
                  <?php $this->load->view('admin/tours/_table',array('bulk_actions'=>true)); ?>
               <?php $this->load->view('admin/tours/_bulk_actions'); ?>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
<?php init_tail(); ?>
<?php $this->load->view('admin/includes/helpers_bottom.php');?>
<?php require('modules/booking/assets/js/tours_manage_js.php'); ?>
<script>
   tourid = '<?php echo $tourid; ?>';
   
   $(function(){
       tours_kanban();
   });
</script>
</body>
</html>
