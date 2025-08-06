<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="col-md-12 no-padding">
						<div class="panel_s">
							<?php echo form_open($this->uri->uri_string()); ?>
							<div class="panel-body">
								<h4 class="no-margin">Vendor Payments</h4>
								<hr class="hr-panel-heading" />
								<?php if(1){ ?>
								<div class="f_client_id">
                                  <div class="col-md-12 form-group">
                                      <label for="vendor"><?php echo _l('vendor'); ?></label>
                                      <select name="vendor" id="vendor" class="selectpicker"; return false;" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>" >
                                          <option value=""></option>
                                          <?php foreach($vendors as $s) { ?>
                                          <option value="<?php echo html_entity_decode($s['userid']); ?>" <?php if(isset($pur_order) && $pur_order->vendor == $s['userid']){ echo 'selected'; }else{ if(isset($ven) && $ven == $s['userid']){ echo 'selected';} } ?>><?php echo html_entity_decode($s['company']); ?></option>
                                            <?php } ?>
                                      </select>              
                                    </div>
                                </div>
								<?php;}?>
								<?php echo render_date_input('date','payment_edit_date',""); ?>
								<?php echo render_select('paymentmode',$payment_modes,array('id','name'),'payment_mode',""); ?>
								<div class="row">
								    <div class="col-md-6">
								        <?php echo render_input('amount','payment_edit_amount_received',"",'number'); ?>
								    </div> 
			                        <div class="col-md-6">
			                            <label for="pinvoices"><?php echo _l('Purchase Invoice'); ?></label>
                                  <select id="a123" name="pur_order[]" class="selectpicker" multiple="1"  data-none-selected-text="<?php echo _l('No Data') ?>" data-width="100%" data-live-search="true" data-actions-box="true">
                                  </select>
								    </div>
								    <div class="col-md-6">
								        <?php //echo render_input('amount','payment_edit_amount_received',"",'number'); ?>
								    </div>
								</div>
								<!--<i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('payment_method_info'); ?>"></i>-->
								<?php echo render_input('paymentmethod','payment_method',""); ?>
								<?php echo render_input('transactionid','payment_transaction_id',""); ?>
								<?php echo render_textarea('note','note',""); ?>
								<div class="btn-bottom-toolbar text-right">
									<button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
								</div>
							</div>
							<?php echo form_close(); ?>
						</div>
					</div>
				</div>
			</div>
			<?php init_tail(); ?>
			<script>
                $('#vendor').change(function () {
                        if ($('#vendor').val() != "") {
                            $.ajax({
                                url: 'purchase/get_pur_order_unpaid',
                                type: 'POST',
                                cache: false,
                                data: {
                                    'vid': $('#vendor').val()
                                },
                                success: function (data) {
                                    <!--console.log(data);-->
                                    s='';
                                    <!--for (var i = 0; i < data.length; i++) {  -->
                                    <!--console.log(data[i]);-->
                                    <!--       s += '<option value="' + data[i].id + '">' + data[i].pur_order_number + '</option>';  -->
                                    <!--   }  -->
                                        $.each(JSON.parse(data), function(id, pur_order_number) {
                                            <!--console.log(pur_order_number.pur_order_number);-->
                                            s+='<option value="'+ pur_order_number.id +'">'+ pur_order_number.pur_order_number +'('+pur_order_number.date+')</option>';
                                        });
                                        $("select[name='pur_order[]']").html('');
                                        $("select[name='pur_order[]']").append(s);
                                       $("select[name='pur_order[]']").selectpicker('refresh');
                                }
                            });
                        }
                    });
				$(function(){
					appValidateForm($('form'),{ amount:'required', date:'required' });
				});
			</script>
		</body>
		</html>
