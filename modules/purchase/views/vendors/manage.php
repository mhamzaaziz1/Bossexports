<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            
            <div class="panel_s">
               <div class="panel-body">
                  <div class="_buttons">
                     <?php if (has_permission('purchase','','create')) { ?>
                     <a href="<?php echo admin_url('purchase/vendor'); ?>" class="btn btn-info mright5 test pull-left display-block">
                     <?php echo _l('new_vendor'); ?></a>
                     <a href="<?php echo admin_url('purchase/all_contacts'); ?>" class="btn btn-info pull-left display-block mright5">
                     <?php echo _l('vendor_contacts'); ?></a>
                     <a href="<?php echo admin_url('purchase/vendor_items'); ?>" class="btn btn-info pull-left display-block mright5">
                     <?php echo _l('vendor_items'); ?></a>
                     <?php } ?>
                     <?php if (1) { ?>
                     <a href="<?php echo admin_url('purchase/vendor_aging'); ?>" class="btn btn-info pull-left display-block mright5">
                     <?php echo _l('Aging List'); ?></a>
                     <?php } ?>
                   
                  
                     <a href="#" class="btn btn-default pull-right mright5" id="show-all-balances">Show All Balances</a>
                  </div>
                 
                  
                  
                  <div class="clearfix mtop20"></div>
                  <div class="row col-md-12"><hr/></div>
                  <?php
                     $table_data = array();
                     $_table_data = array(
                      '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="vendors"><label></label></div>',
                       array(
                         'name'=>_l('the_number_sign'),
                         'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-number')
                        ),
                         array(
                         'name'=>_l('clients_list_company'),
                         'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-company')
                        ),
                         array(
                         'name'=>_l('contact_primary'),
                         'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-primary-contact')
                        ),
                         array(
                         'name'=>_l('company_primary_email'),
                         'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-primary-contact-email')
                        ),
                        array(
                         'name'=>_l('clients_list_phone'),
                         'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-phone')
                        ),
                         array(
                         'name'=>_l('customer_active'),
                         'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-active')
                        ),
              
                        array(
                         'name'=>_l('Balance'),
                         'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-date-created')
                        ),
                      );
                     foreach($_table_data as $_t){
                      array_push($table_data,$_t);
                     }

                     $custom_fields = get_custom_fields('vendors',array('show_on_table'=>1));
                     foreach($custom_fields as $field){
                      array_push($table_data,$field['name']);
                     }

                     render_datatable($table_data,'vendors',[],[
                           'data-last-order-identifier' => 'vendors',
                           'data-default-order'         => get_table_last_order('vendors'),
                     ]);
                     ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script>
   $(function(){
       var tAPI = initDataTable('.table-vendors', admin_url+'purchase/table_vendor', [0], [0], [],<?php echo hooks()->apply_filters('vendors_table_default_order', json_encode(array(1,'asc'))); ?>);
       
       $('body').on('click', '.vendor-balance-check', function() {
           var vendorID = $(this).data('id');
           var $btn = $(this);
           $btn.html('<i class="fa fa-refresh fa-spin"></i>');
           $.get(admin_url + 'purchase/get_vendor_balance/' + vendorID, function(response) {
               $btn.parent().html(response);
           });
       });

       $('#show-all-balances').on('click', function(e) {
           e.preventDefault();
           var items = $('.vendor-balance-check').toArray();
           processItems(items);
       });

       function processItems(items) {
           if (items.length === 0) return;

           var item = items.shift();
           var $item = $(item);
           var vendorID = $item.data('id');
           
           $item.html('<i class="fa fa-refresh fa-spin"></i>');
           $.get(admin_url + 'purchase/get_vendor_balance/' + vendorID, function(response) {
               $item.parent().html(response);
               processItems(items);
           });
       }
   });
</script>
</body>
</html>
