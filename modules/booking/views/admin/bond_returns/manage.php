<?php

defined('BASEPATH') or exit('No direct script access allowed'); 

init_head();
?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
            <div class="panel_s">
              <div class="panel-body">
                  <div class="col-md-1" style="margin-bottom: 30px; padding-top:5px;">
                      <span style="float:right;">Date Range : </span>
                  </div>
                  <div class="col-md-3">
                      <input id="custom_range" name="custom_range" class="form-control input-sm" aria-controls="DataTables_Table_0">
                    </div>
                    <div class= "col-md-8"></div>
             <?php echo form_hidden('custom_view'); ?>
             <?php $this->load->view('admin/bond_returns/table_html'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<?php 
require('modules/booking/assets/js/bookings_manage_js.php'); 
?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function(){
     var Bond_returnsServerParams = {};

     $.each($('._hidden_inputs._filters input'),function(){
         Bond_returnsServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
     });

     initDataTable('.table-bond_returns', admin_url+'booking/bond_returns/table/?range=<?php echo $start_date . " - ".$end_date;?>', undefined, undefined, Bond_returnsServerParams, <?php echo hooks()->apply_filters('bond_returns_table_default_order', json_encode(array(0,'asc'))); ?>);

     init_ajax_search('customer', '#clientid_copy_bond_return.ajax-search');
     $("#custom_range").daterangepicker({
         autoApply: true,
         autoUpdateInput: true,
         locale: {
            format:'DD/MM/YYYY',
         }
     });
     $("#custom_range").data('daterangepicker').setStartDate('<?php echo $start_date;?>');
     $("#custom_range").data('daterangepicker').setEndDate('<?php echo $end_date;?>');
     $("#custom_range").on('apply.daterangepicker', function (e, picker){
         window.location.href = admin_url+'booking/bond_returns/?range=' + $("#custom_range").val();
     })
});
</script>
</body>
</html>
