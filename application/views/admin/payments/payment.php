<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-5">
				<div class="panel_s">
					<div class="col-md-12 no-padding">
						<div class="panel_s">
							<?php echo form_open($this->uri->uri_string()); ?>
							<div class="panel-body">
								<h4 class="no-margin"><?php echo _l('payment_edit_for_invoice'); ?> <a href="<?php echo admin_url('invoices/list_invoices/'.$payment->invoiceid); ?>"><?php echo format_invoice_number($payment->invoice->id); ?></a></h4>
								<hr class="hr-panel-heading" />
								<?php if(1){ ?>
								<div class="f_client_id">
                                  <div class="form-group select-placeholder">
                                    <label for="clientid" class="control-label"><?php echo _l('invoice_select_customer'); ?></label>
                                    <?php
                                    if(empty($invoice->clientid) || $invoice->clientid==0){
                                        $client=$payment->client_id;
                                    }else{
                                        $client=$invoice->clientid;
                                    }
                                    // var_dump($payment->client_id); die;
                                    ?>
                                    <select id="clientid" name="client_id" data-live-search="true" data-width="100%" class="ajax-search<?php if(empty($client) ){echo ' customer-removed';} ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                   <?php
                                   
                                   $selected = $client;
                                     if($selected == ''){
                                       $selected = (isset($customer_id) ? $customer_id: '');
                                     }
                                     if($selected != ''){
                                        $rel_data = get_relation_data('customer',$selected);
                                        $rel_val = get_relation_values($rel_data,'customer');
                                        echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
                                     } ?>
                                    </select>
                                  </div>
                                </div>
								<?php } ?>
								<?php echo render_input('amount','payment_edit_amount_received',$payment->amount,'number'); ?>
								<?php echo render_date_input('date','payment_edit_date',_d($payment->date)); ?>
								<?php echo render_select('paymentmode',$payment_modes,array('id','name'),'payment_mode',$payment->paymentmode); ?>
								<div class="row">
			                        <div class="col-md-12">
			                            <label for="pinvoices"><?php echo _l('Invoices'); ?></label>
			                            <?php
			                            
			                            ?>
                                  <select id="a123" name="pur_order[]" class="selectpicker" multiple="1"  data-none-selected-text="<?php echo _l('No Data') ?>" data-width="100%" data-live-search="true" data-actions-box="true">
                                  </select>
								    </div>
								</div>
								<?php //echo render_select('paymentmode',$payment_modes,array('id','name'),'invoice',$payment->paymentmode); ?>
								<i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('payment_method_info'); ?>"></i>
								<?php echo render_input('paymentmethod','payment_method',$payment->paymentmethod); ?>
								<?php echo render_input('transactionid','payment_transaction_id',$payment->transactionid?$payment->transactionid:date('YmdHis')); ?>
								<?php echo render_textarea('note','note',$payment->note,array('rows'=>7)); ?>
								<div class="btn-bottom-toolbar text-right">
									<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
								</div>
							</div>
							<?php echo form_close(); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-7">
				<div class="panel_s">
					<div class="panel-body">
						<h4 class="pull-left "><?php echo _l('payment_view_heading'); ?></h4>
						<div class="pull-right">
							<div class="btn-group">

								<a href="#" data-toggle="modal" data-target="#payment_send_to_client"
								class="payment-send-to-client btn-with-tooltip btn btn-default">
									<i class="fa fa-envelope"></i></span>
								</a>

								<a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<i class="fa fa-file-pdf-o"></i>
									<?php if(is_mobile()){echo ' PDF';} ?> <span class="caret"></span>
								</a>

								<ul class="dropdown-menu dropdown-menu-right">
									<li class="hidden-xs">
										<a href="<?php echo admin_url('payments/pdf/'.$payment->paymentid.'?output_type=I'); ?>">
											<?php echo _l('view_pdf'); ?>
										</a>
									</li>
									<li class="hidden-xs">
										<a href="<?php echo admin_url('payments/pdf/'.$payment->paymentid.'?output_type=I'); ?>" target="_blank">
											<?php echo _l('view_pdf_in_new_window'); ?>
										</a>
									</li>
									<li>
										<a href="<?php echo admin_url('payments/pdf/'.$payment->paymentid); ?>">
											<?php echo _l('download'); ?>
										</a>
									</li>
									<li>
										<a href="<?php echo admin_url('payments/pdf/'.$payment->paymentid.'?print=true'); ?>" target="_blank">
											<?php echo _l('print'); ?>
										</a>
									</li>
								</ul>
							</div>
							<?php if(has_permission('payments','','delete')){ ?>
								<a href="<?php echo admin_url('payments/delete/'.$payment->paymentid); ?>" class="btn btn-danger _delete">
									<i class="fa fa-remove"></i>
								</a>
							<?php } ?>
						</div>
						<div class="clearfix"></div>
						<hr class="hr-panel-heading" />
						<div class="row">
							<div class="col-md-6 col-sm-6">
								<address>
									<?php echo format_organization_info(); ?>
								</address>
							</div>
							<?php
							if($payment->invoice == ""){
							           $rel_data = get_relation_data('customer',$selected);
                                       $rel_val = get_relation_values($rel_data,'customer');
                                   }
							?>
							<div class="col-sm-6 text-right">
								<address>
									<span class="bold">
									    <?php if($payment->invoice != "" && $payment->invoice != 0){?>
										<?php echo format_customer_info($payment->invoice, 'payment', 'billing', true); ?>
										<?php }else{
										echo "<h3>Customer Detail</h3>";
										echo "Name: ".$rel_val['name'];
										?>
										
										<?php }?>
									</address>
								</div>
							</div>
							<div class="col-md-12 text-center">
								<h3 class="text-uppercase"><?php echo _l('payment_receipt'); ?></h3>
							</div>
							<div class="col-md-12 mtop30">
								<div class="row">
									<div class="col-md-6">
										<p><?php echo _l('payment_date'); ?> <span class="pull-right bold"><?php echo _d($payment->date); ?></span></p>
										<hr />
										<p><?php echo _l('payment_view_mode'); ?>
										<span class="pull-right bold">
											<?php echo $payment->name; ?>
											<?php if(!empty($payment->paymentmethod)){
												echo ' - ' . $payment->paymentmethod;
											}
											?>
										</span></p>
										<?php if(!empty($payment->transactionid)) { ?>
											<hr />
											<p><?php echo _l('payment_transaction_id'); ?>: <span class="pull-right bold"><?php echo $payment->transactionid; ?></span></p>
										<?php } ?>
									</div>
									<div class="clearfix"></div>
									<div class="col-md-6">
										<div class="payment-preview-wrapper">
											<?php echo _l('payment_total_amount'); ?><br />
											<?php echo app_format_money($payment->amount, $payment->invoice->currency_name); ?>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-12 mtop30">
								<h4><?php echo _l('payment_for_string'); ?></h4>
								<div class="table-responsive">
									<table class="table table-borderd table-hover">
										<thead>
											<tr>
												<th><?php echo _l('payment_table_invoice_number'); ?></th>
												<th><?php echo _l('payment_table_invoice_date'); ?></th>
												<th><?php echo _l('payment_table_invoice_amount_total'); ?></th>
												<th><?php echo _l('payment_table_payment_amount_total'); ?></th>
												<?php if($payment->invoice->status != Invoices_model::STATUS_PAID
													&& $payment->invoice->status != Invoices_model::STATUS_CANCELLED) { ?>
														<th><span class="text-danger"><?php echo _l('invoice_amount_due'); ?></span></th>
													<?php } ?>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><?php echo format_invoice_number($payment->invoice->id); ?></td>
													<td><?php echo _d($payment->invoice->date); ?></td>
													<td><?php echo app_format_money($payment->invoice->total, $payment->invoice->currency_name); ?></td>
													<td><?php echo app_format_money($payment->amount, $payment->invoice->currency_name); ?></td>
													<?php if($payment->invoice->status != Invoices_model::STATUS_PAID
														&& $payment->invoice->status != Invoices_model::STATUS_CANCELLED) { ?>
															<td class="text-danger">
																<?php echo app_format_money(get_invoice_total_left_to_pay($payment->invoice->id, $payment->invoice->total), $payment->invoice->currency_name); ?>
															</td>
														<?php } ?>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="btn-bottom-pusher"></div>
				</div>
			</div>
			<?php $this->load->view('admin/payments/send_to_client'); ?>
			<?php init_tail(); ?>
			<script>
			jQuery(document).ready(function($) {
    // Function to load unpaid invoices
    function loadUnpaidInvoices(clientId) {
        if (clientId != "") {
            $.ajax({
                url: 'https://app.bossexports.co.za/admin/payments/get_invoice_unpaid',
                type: 'POST',
                cache: false,
                data: {
                    'vid': clientId
                },
                success: function(data) {
                    var s = '';
                    $.each(JSON.parse(data), function(id, pur_order_number) {
                        s += '<option value="' + pur_order_number.id + '">' + pur_order_number.pur_order_number + '</option>';
                    });
                    $("select[name='pur_order[]']").html(s).selectpicker('refresh');
                }
            });
        } else {
            $("select[name='pur_order[]']").html('').selectpicker('refresh');
        }
    }

    // On change of clientid dropdown
    $('#clientid').change(function() {
        loadUnpaidInvoices($(this).val());
    });

    // Validate form
    appValidateForm($('form'), { amount: 'required', date: 'required', transactionid: 'required' });

    // Load unpaid invoices on page load
    loadUnpaidInvoices($('#clientid').val());
});

			</script>
		</body>
		</html>
