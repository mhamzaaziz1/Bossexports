<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">
			    <?php if(has_permission('payments','','create')){ ?>
                  <a href="<?php echo admin_url('payments/payment/-1'); ?>" class="btn btn-success">
                    <i class="fa fa-plus-square"></i> <?php echo _l('payment'); ?></a>
                     <a href="<?php echo admin_url('payments/all_payment'); ?>" class="btn btn-success">
                     <?php echo _l('All payment'); ?></a>
                  <?php } ?>
                  <br><br>
                  <div class="row">
                     <div class="col-md-3">
                        <div class="form-group">
                           <label for="payment_from" class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
                           <div class="input-group date">
                              <input type="text" class="form-control datepicker" id="payment_from" name="payment_from">
                              <div class="input-group-addon">
                                 <i class="fa fa-calendar calendar-icon"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="form-group">
                           <label for="payment_to" class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
                           <div class="input-group date">
                              <input type="text" class="form-control datepicker" id="payment_to" name="payment_to">
                              <div class="input-group-addon">
                                 <i class="fa fa-calendar calendar-icon"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <div class="form-group">
                           <label for="amount" class="control-label"><?php echo _l('amount'); ?></label>
                           <input type="number" class="form-control" id="amount" name="amount">
                        </div>
                     </div>
                     <div class="col-md-2 mtop25">
                        <button type="button" id="filter-payments" class="btn btn-info"><?php echo _l('filter'); ?></button>
                     </div>
                  </div>
                  <hr class="hr-panel-heading" />
				<?php $this->load->view('admin/payments/table_html'); ?>
			</div>
		</div>
	</div>
</div>
<?php init_tail(); ?>

<script>
	$(function(){
		// Set today's date as default if no date is selected
		var today = new Date();

		// Only set default dates if they're not already set
		if (!$('input[name="payment_from"]').val()) {
			$('input[name="payment_from"]').datepicker('setDate', today);
		}
		if (!$('input[name="payment_to"]').val()) {
			$('input[name="payment_to"]').datepicker('setDate', today);
		}

		// Define server parameters for the datatable
		var fnServerParams = {
			"payment_from": 'input[name="payment_from"]',
			"payment_to": 'input[name="payment_to"]',
			"amount": 'input[name="amount"]'
		};

		// Initialize the datatable with the server parameters
		var paymentsTable = initDataTable('.table-payments', admin_url+'payments/table', undefined, undefined, fnServerParams, <?php echo hooks()->apply_filters('payments_table_default_order', json_encode(array(0,'desc'))); ?>);

		// Filter button click handler
		$('#filter-payments').on('click', function() {
			// Ensure the date values are set before filtering
			if (!$('input[name="payment_from"]').val()) {
				$('input[name="payment_from"]').datepicker('setDate', today);
			}
			if (!$('input[name="payment_to"]').val()) {
				$('input[name="payment_to"]').datepicker('setDate', today);
			}

			// Reload the datatable with the new filter parameters
			paymentsTable.ajax.reload();
		});
	});
</script>
</body>
</html>
