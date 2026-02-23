<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<div id="wrapper">
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">
			    <?php if(has_permission('payments','','create')){ ?>
                  <a href="<?php echo admin_url('payments/payment/-1'); ?>" class="btn btn-success">
                    <i class="fa fa-plus-square"></i> <?php echo _l('payment'); ?></a>
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
				<?php $this->load->view('admin/payments/table_html_all'); ?>
			</div>
		</div>
		<!-- Modal -->
        <div class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="noteModalLabel">Add Note</h4>
              </div>
              <div class="modal-body">
                <form>
                  <div class="form-group">
                    <label for="note">Note:</label>
                    <textarea class="form-control" id="note" name="note"></textarea>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save-note">Save Note</button>
              </div>
            </div>
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
		var paymentsTable = initDataTable('.table-payments', admin_url+'payments/table_all', undefined, undefined, fnServerParams, <?php echo hooks()->apply_filters('payments_table_default_order', json_encode(array(0,'desc'))); ?>);

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

  $('#noteModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    var modal = $(this);
    modal.find('.modal-body #note').val('');
    modal.find('.modal-footer #save-note').unbind('click').click(function() {
      var note = modal.find('.modal-body #note').val();
      // Send an AJAX request to save the note
      $.ajax({
        type: "POST",
        url: "save_note.php", // Replace with your PHP script
        data: {id: id, note: note},
        success: function(data) {
          modal.modal('hide');
          alert("Note saved successfully!");
        }
      });
    });
  });

</script>
</body>
</html>
