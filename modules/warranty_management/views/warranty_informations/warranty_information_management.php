<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
	.warranty-btn{
		width: 365px;
	}
</style>

<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12" id="small-table">
				<div class="panel_s">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-12">
								<h4 class="no-margin h4-color"><i class="fa fa-clone menu-icon menu-icon" aria-hidden="true"></i> <?php echo _l($title); ?></h4>
							</div>
						</div>
						<hr class="hr-color">

						<div class="row">

							<div class=" col-md-3 ">
								<div class="form-group">
									<select name="client_filter[]" id="client_filter" class="selectpicker" multiple="true" data-actions-box="true" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('clients'); ?>">

										<?php foreach ($clients as $client) { ?>
											<option value="<?php echo new_html_entity_decode($client['userid']); ?>"><?php echo new_html_entity_decode($client['company']); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>

							<div class=" col-md-3 ">
								<div class="form-group">
									<select name="order_filter[]" id="order_filter" class="selectpicker" multiple="true" data-actions-box="true" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('sm_order'); ?>">

										<?php foreach ($orders as $order) { ?>
											<option value="<?php echo new_html_entity_decode($order['id']); ?>"><?php echo new_html_entity_decode($order['order_code']); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class=" col-md-3 ">
								<div class="form-group">
									<select name="delivery_note_filter[]" id="delivery_note_filter" class="selectpicker" multiple="true" data-actions-box="true" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('wm_inventory_delivery_voucher'); ?>">

										<?php foreach ($goods_deliveries as $goods_delivery) { ?>
											<option value="<?php echo new_html_entity_decode($goods_delivery['id']); ?>"><?php echo new_html_entity_decode($goods_delivery['goods_delivery_code']); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>

							<div class=" col-md-3 ">
								<div class="form-group">
									<select name="product_filter[]" id="product_filter" class="selectpicker" multiple="true" data-actions-box="true" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('sm_service_name'); ?>">

										<?php foreach ($products as $product) { ?>
											<option value="<?php echo new_html_entity_decode($product['itemid']); ?>"><?php echo new_html_entity_decode($product['description']); ?></option>
										<?php } ?>
									</select>
								</div>
							</div>

						</div>

						<div class="row">
							<div class="col-md-3">
								<button id="showExpiredWarranty" type="button" class="warranty-btn tw-bg-white tw-border tw-border-solid tw-border-neutral-300/80 tw-shadow-sm tw-py-2 tw-px-3.5 tw-rounded-lg tw-text-sm hover:tw-bg-neutral-100 tw-text-neutral-600 hover:tw-text-neutral-600 focus:tw-text-neutral-600 text-left odd:last:tw-col-span-2 md:odd:last:tw-col-auto" fdprocessedid="w4r88">
									<div class="tw-flex tw-items-center">
										<span class="tw-font-normal tw-text-base tw-inline-flex tw-items-center text-danger"> Warranty End </span>
										<span class="tw-ml-2 rtl:tw-mr-2 tw-text-xs tw-text-neutral-500 tw-mt-px"> </span>
									</div>
									<div class="tw-mt-0.5">
										<div class="tw-text-neutral-600">
											<span class="tw-font-semibold"></span>
										</div>
									</div>
								</button>
							</div>

							<div class="col-md-3">
									<button type="button" id="showActiveWarranty" class="warranty-btn tw-bg-white tw-border tw-border-solid tw-border-neutral-300/100 tw-shadow-sm tw-py-2 tw-px-3.5 tw-rounded-lg tw-text-sm hover:tw-bg-neutral-100 tw-text-neutral-600 hover:tw-text-neutral-600 focus:tw-text-neutral-600 text-left odd:last:tw-col-span-2 md:odd:last:tw-col-auto" fdprocessedid="w4r88">
										<div class="tw-flex tw-items-center">
											<span class="tw-font-normal tw-text-base tw-inline-flex tw-items-center text-success"> Warranties </span>
										</div>
										<div class="tw-mt-0.5">
											<div class="tw-text-neutral-600">
												<span class="tw-font-semibold"></span>
											</div>
										</div>
									</button>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-md-12">

								<?php
								$table_data = array(
									_l('id'),
									_l('client'),
									_l('project_name'),
									_l('wm_order_number_delivery_note'),
									_l('wm_invoice'),
									_l('wm_product_service_name'),
									_l('wm_rate'),
									_l('wm_quantity'),
									_l('wm_expriry_date'),
									_l('wm_lot_number'),
									_l('wm_serial_number'),
									_l('wm_warranty_period'),
									_l('warranty_status'), 
									_l('wm_options'),

								);

								render_datatable(
									$table_data,
									'warranty_information_table',
									array('customizable-table'),
									array(
										'proposal_sm' => 'proposal_sm',
										'id' => 'table-warranty_information_table',
										'data-last-order-identifier' => 'warranty_information_table',
										'data-default-order' => get_table_last_order('warranty_information_table'),
									)
								); ?>

							</div>
						</div>

					</div>
				</div>
			</div>



		</div>
	</div>

</div>
<div id="modal_wrapper"></div>

<div class="modal fade" id="sendWarrantyEmailModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<h4 class="modal-title">
					<i class="fa fa-envelope"></i> Send Warranty to Email
				</h4>
				<button type="button" class="close" data-dismiss="modal">
					<span>&times;</span>
				</button>
			</div>

			<?php echo form_open(admin_url('warranty_management/send_to_email')); ?>

			<div class="modal-body">
				<input type="hidden" name="warranty_id" id="email_warranty_id">

				<div class="form-group">
					<label>Email</label>
					<input type="email" name="email" class="form-control" required>
				</div>
				<div class="form-group">
					<label>Email Message</label>
					<textarea name="email_message" class="form-control tinymce" rows="6" placeholder="Write message to customer...">
							Hi  {customer_name} ,
							<br><br>

							Please find attached the warranty document for your reference.  <br> <br>
							This certificate confirms the warranty coverage for the supplied item(s). <br> <br>

							For any assistance or clarification, feel free to contact us. <br> <br>

							Best Regards,  <br>
							{email_signature}

						</textarea>
				</div>

			</div>

			<div class="modal-footer">
				<button class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button class="btn btn-primary">Send</button>
			</div>

			<?php echo form_close(); ?>

		</div>
	</div>
</div>






<?php init_tail(); ?>
<?php require 'modules/warranty_management/assets/js/warranty_informations/warranty_information_manage_js.php'; ?>
</body>

<script>
	function openSendWarrantyEmailModal(warranty_id) {
		$('#email_warranty_id').val(warranty_id);
		$('#sendWarrantyEmailModal').modal('show');
	}

	$(document).ready(function(){

		// $(document).on('click', '#showExpiredWarranty', function () {

			console.log("ali clicked");

		// 	$(this).toggleClass('active-filter');

		// 	$('#showActiveWarranty').removeClass('active-filter');

		// 	$('table.table-warranty_information_table')
		// 		.DataTable()
		// 		.ajax.reload(null,false);

		// });

		// $(document).on('click', '#showActiveWarranty', function () {

			console.log("active clicked");

		// 	$(this).toggleClass('active-filter');

		// 	$('#showExpiredWarranty').removeClass('active-filter');

		// 	$('table.table-warranty_information_table')
		// 		.DataTable()
		// 		.ajax.reload(null,false);
		// });

		$('#showExpiredWarranty').on('click', function () {
			console.log("ali clicked");
			$('#showActiveWarranty').removeClass('active-filter');
			$(this).addClass('active-filter');
			$('.table-warranty_information_table').DataTable().ajax.reload(null,false);
		});

		$('#showActiveWarranty').on('click', function () {
				console.log("active clicked");
			$('#showExpiredWarranty').removeClass('active-filter');
			$(this).addClass('active-filter');
			$('.table-warranty_information_table').DataTable().ajax.reload(null,false);
		});

	});

</script>

</html>