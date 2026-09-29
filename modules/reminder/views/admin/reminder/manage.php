<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="_filters _hidden_inputs">
            <?php
            if(isset($statuses) && !empty($statuses)){
                foreach($statuses as $_status){
                    $val = '';
                    if($_status == $this->input->get('status')){
                      $val = $_status;
                  }
                  echo form_hidden('reminder_'.$_status,$val);
              } 
          }
          foreach($years as $year){
            echo form_hidden('year_'.$year['year'],$year['year']);
        }
        foreach($reminder_sale_agents as $agent){
            echo form_hidden('sale_agent_'.$agent['sale_agent']);
        }
        foreach($customers as $cust){
            echo form_hidden('customer_'.$cust['userid']);
        }
        echo form_hidden('leads_related');
        echo form_hidden('customers_related');
        echo form_hidden('expired');
        echo form_hidden('complete_reminder','1');
        $reminder_filter_number_val = !empty($this->session->userdata['reminder_filter_number']) ? $this->session->userdata['reminder_filter_number'] : '';
        echo form_hidden('reminder_filter_number', $reminder_filter_number_val);
        $reminder_filter_date_f_val = !empty($this->session->userdata['reminder_filter_date_f']) ? $this->session->userdata['reminder_filter_date_f'] : '';
        echo form_hidden('reminder_filter_date_f', $reminder_filter_date_f_val);
        $reminder_filter_date_t_val = !empty($this->session->userdata['reminder_filter_date_t']) ? $this->session->userdata['reminder_filter_date_t'] : '';
        echo form_hidden('reminder_filter_date_t', $reminder_filter_date_t_val);
        $reminder_filter_related_val = !empty($this->session->userdata['reminder_filter_related']) ? $this->session->userdata['reminder_filter_related'] : '';
        echo form_hidden('reminder_filter_related', $reminder_filter_related_val);
        $reminder_filter_company_val = !empty($this->session->userdata['reminder_filter_company']) ? $this->session->userdata['reminder_filter_company'] : '';
        echo form_hidden('reminder_filter_company', $reminder_filter_company_val);
        $reminder_filter_contact_val = !empty($this->session->userdata['reminder_filter_contact']) ? $this->session->userdata['reminder_filter_contact'] : '';
        echo form_hidden('reminder_filter_contact', $reminder_filter_contact_val);
        $reminder_filter_description_val = !empty($this->session->userdata['reminder_filter_description']) ? $this->session->userdata['reminder_filter_description'] : '';
        echo form_hidden('reminder_filter_description', $reminder_filter_description_val);
        $reminder_filter_assigned_val = !empty($this->session->userdata['reminder_filter_assigned']) ? $this->session->userdata['reminder_filter_assigned'] : '';
        echo form_hidden('reminder_filter_assigned', $reminder_filter_assigned_val);
        ?>
    </div>
    <div class="col-md-12">
       <div class="row">
           <div class="col-md-12" id="small-table">
              <div class="panel_s">
                 <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="reminder_buttons col-md-4">
                                <?php if(has_permission('reminder','','create')){ ?>
                                  <a data-toggle="modal" data-target="#reminderAddModal" class="btn btn-info pull-left display-block">
                                      <?php echo _l('reminder_new'); ?>
                                  </a>
                              <?php } ?> 
                          </div>
                          <div class="col-md-7">
                            <div class="checkbox checkbox-info mbot20 no-mtop reminderbtn_complete text-right">
                               <input type="checkbox" name="show_complete" value="1" id="show_complete" class="complete_data reminderbtn_complete">
                               <label for="show_complete"><?php echo _l('show_complete'); ?></label>
                           </div>     
                       </div>
                       <div class="col-md-1">
                          <div class="display-block text-right">
                            <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                             <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <i class="fa fa-filter" aria-hidden="true"></i>
                          </button>
                          <ul class="dropdown-menu width300">
                             <li>
                                <a href="#" data-cview="all" onclick="dt_custom_view('','.table-reminder',''); return false;">
                                    <?php echo _l('proposals_list_all'); ?>
                                </a>
                            </li>
                            <li class="divider"></li>
                            <?php if(count($years) > 0){ ?>
                             <li class="divider"></li>
                             <?php foreach($years as $year){ ?>
                                 <li class="active">
                                    <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-reminder','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                                </a>
                            </li>
                        <?php } ?>
                    <?php } ?>
                    <?php if(count($reminder_sale_agents) > 0){ ?>
                     <div class="clearfix"></div>
                     <li class="divider"></li>
                     <li class="dropdown-submenu pull-left">
                        <a href="#" tabindex="-1"><?php echo _l('sale_agent_string'); ?></a>
                        <ul class="dropdown-menu dropdown-menu-left">
                           <?php foreach($reminder_sale_agents as $agent){ ?>
                               <li>
                                  <a href="#" data-cview="sale_agent_<?php echo $agent['sale_agent']; ?>" onclick="dt_custom_view('sale_agent_<?php echo $agent['sale_agent']; ?>','.table-reminder','sale_agent_<?php echo $agent['sale_agent']; ?>'); return false;"><?php echo get_staff_full_name($agent['sale_agent']); ?>
                              </a>
                          </li>
                      <?php } ?>
                  </ul>
              </li>
          <?php } ?>
          <?php if(count($customers) > 0){ ?>
             <div class="clearfix"></div>
             <li class="divider"></li>
             <li class="dropdown-submenu pull-left">
                <a href="#" tabindex="-1"><?php echo _l('customers'); ?></a>
                <ul class="dropdown-menu dropdown-menu-left">
                   <?php foreach($customers as $cust){ ?>
                       <li>
                          <a href="#" data-cview="customer_<?php echo $cust['userid']; ?>" onclick="dt_custom_view('customer_<?php echo $cust['userid']; ?>','.table-reminder','customer_<?php echo $cust['userid']; ?>'); return false;"><?php echo $cust['company']; ?>
                      </a>
                  </li>
              <?php } ?>
          </ul>
      </li>
  <?php } ?>
          <div class="clearfix"></div>
            <li class="divider"></li>
         </ul>
       </div>
     </div>
   </div>
 </div>
</div>
<a data-table=".table-reminder" href="javascript:void(0)" id="all_filter_search" class="btn btn-primary table-btn hide btn-search-filters">Search</a>
<a data-table=".table-reminder" href="javascript:void(0)" id="all_filter_reset" class="btn btn-danger table-btn hide btn-search-filters">Reset</a>
<?php echo form_hidden('reminderid',$reminderid);?>
        <?php
        $table_data = array(
          _l('reminder_date'),
          _l('reminder_assigned'),
          _l('reminder_customer'),
          _l('reminder_contact'),
          _l('reminder_description'),
          _l('reminder_rel_type'),
        );
        $custom_fields = get_custom_fields('reminder',array('show_on_table'=>1));
        foreach($custom_fields as $field){
            array_push($table_data,$field['name']);
        }
        $table_data = hooks()->apply_filters('reminder_table_columns', $table_data);
        render_datatable($table_data,'reminder',[],[
         'data-last-order-identifier' => 'reminder',
         'data-default-order'         => get_table_last_order('reminder'),
        ]);
        ?>
      </div>
   </div>
</div>
        <div class="col-md-12 small-table-right-col">
          <div id="diaries" class="hide">
        </div>
       </div>
      </div>
     </div>
    </div>
  </div>
</div>
      <div id="reminderModalData"></div>
       <?php $this->load->view('admin/includes/modals/reminder_add_file'); ?>
      <script>
        var hidden_columns = [3,4,5];
        var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>',csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
      </script>
      <?php init_tail(); ?>
      <script type="text/javascript" src="<?php echo module_dir_url('reminder','assets/manage.js') ?>"></script>
  </body>
  </html>
