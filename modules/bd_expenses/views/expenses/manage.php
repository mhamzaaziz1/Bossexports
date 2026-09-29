<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <div class="panel-body _buttons">
                  <?php if(has_permission('bd_expenses','','create')){ ?>
                  <a href="<?php echo admin_url('bd_expenses/expense'); ?>" class="btn btn-info"><?php echo _l('new_expense'); ?></a>
                  <?php } ?>
                  <?php $this->load->view('expenses/filter_by_template'); ?>
                  <a href="#" onclick="slideToggle('#stats-top'); return false;" class="pull-right btn btn-default mleft5 btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('view_stats_tooltip'); ?>"><i class="fa fa-bar-chart"></i></a>
                  <a href="#" class="btn btn-default pull-right btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-expenses','#expense'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
                  <div id="stats-top" class="hide">
                     <hr />
                     <div id="bd_expenses_total"></div>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12" id="small-table">
                  <div class="panel_s">
                     <div class="panel-body">
                        <div class="clearfix"></div>
                        <!-- if expenseid found in url -->
                        <?php echo form_hidden('expenseid',$expenseid); ?>
                        <?php $this->load->view('expenses/table_html'); ?>
                     </div>
                  </div>
               </div>
               <div class="col-md-7 small-table-right-col">
                  <div id="expense" class="hide">
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<script>var hidden_columns = [4,5,6,7,8,9];</script>
<?php init_tail(); ?>
<script>
   Dropzone.autoDiscover = false;
   $(function(){
             // Expenses additional server params
             var Expenses_ServerParams = {};
             $.each($('._hidden_inputs._filters input'),function(){
               Expenses_ServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
             });
             initDataTable('.table-expenses', admin_url+'bd_expenses/table', 'undefined', 'undefined', Expenses_ServerParams, <?php echo hooks()->apply_filters('expenses_table_default_order', json_encode(array(5,'desc'))); ?>).column(0).visible(false, false).columns.adjust();

             init_bd_expense();
			 init_bd_expenses_total();
	});
function init_bd_expense(id) {
    load_small_table_item(id, '#expense', 'expenseid', 'bd_expenses/get_expense_data_ajax', '.table-expenses');
}	
// Expenses quick total stats
function init_bd_expenses_total() {

    if ($('#bd_expenses_total').length === 0) { return; }
    var currency = $("body").find('select[name="expenses_total_currency"]').val();
    var _years = $("body").find('select[name="expenses_total_years"]').selectpicker('val');
    var years = [];
    $.each(_years, function(i, _y) {
        if (_y !== '') { years.push(_y); }
    });

    var customer_id = '';
    var _customer_id = $('.customer_profile input[name="userid"]').val();
    if (typeof(customer_id) != 'undefined') { customer_id = _customer_id; }

    var project_id = '';
    var _project_id = $('input[name="project_id"]').val();
    if (typeof(project_id) != 'undefined') { project_id = _project_id; }

    $.post(admin_url + 'bd_expenses/get_expenses_total', {
        currency: currency,
        init_total: true,
        years: years,
        customer_id: customer_id,
        project_id: project_id,
    }).done(function(response) {
        $('#bd_expenses_total').html(response);
    });
}
</script>
</body>
</html>
