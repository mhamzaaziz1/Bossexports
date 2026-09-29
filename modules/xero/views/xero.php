<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<?php init_head();?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body">
			            <h3 class="text-info no-margin"><img height="50" src="<?php echo module_dir_url('xero', 'assets/imgs/xero.png'); ?>"> <?php echo _l('xero'); ?></h3>
			        </div>
			    </div>
				<div class="panel_s">
					<div class="panel-body">
			            <ul class="nav nav-tabs">
		                  <li class="active"><a data-toggle="tab" href="#xero_chat_of_acc">Chart of Accounts</a></li>
		                  <li><a data-toggle="tab" href="#xero_ac_synchs"><i class="fa fa-refresh"></i> Synchronizations</a></li>
		                </ul>
		                <div class="tab-content">
		                	<div id="xero_chat_of_acc" class="tab-pane fade in active">
		                		<div class="clearfix"></div>
					              <?php
echo '<table id="xero_ledgertable" class="table table-striped dataTable no-footer dtr-inline">';
echo '<thead>';
echo '<th>' . _l('account_code') . '</th>';
echo '<th>' . _l('account_name') . '</th>';
echo '<th>' . _l('account_type') . '</th>';
echo '<th>Code</th>';
$currency = !empty($accounts) ? $accounts->currency : '';
echo '</thead>';
echo "<tbody>";
$chart = !empty($accounts) ? $accounts->chart : array();
echo print_xero_account_chart($chart);
echo "</tbody>";
echo '</table>';
?>
		                	</div>
		                	<div id="xero_ac_synchs" class="tab-pane fade">
		                	<?php if (get_option('xero_access_token') && time() < get_option('xero_expiration_date')) {?>
		                		<div class="alert alert-info">
		                			<?=_l('xero_sync_helper');?>
		                		</div>
							    <div class="row">
							        <div class="col-md-4 xero_sub">
							            <div class="panel_s">
							                <div class="panel-heading">Chart Of Accounts<strong class="pull-right" data-toggle="tooltip" data-title="<?php echo _l('xero_download_helper_2'); ?>"><i class="text-warning fa-2x fa fa-download" ></i></strong></div>
							                <div class="panel-body">
							                    <button class="btn btn-info" id="synch_chart_of_accounts" data-xero_synch_area="true"><i class="fa fa-refresh"></i> Synchronize</button>
							                </div>
							            </div>
							        </div>
							        <div class="col-md-4 xero_sub">
							            <div class="panel_s">
							                <div class="panel-heading">Customers<strong class="pull-right" data-toggle="tooltip" data-title="<?php echo _l('xero_upload_helper_2'); ?>"><i class="text-info fa-2x fa fa-upload"></i></strong></div>
							                <div class="panel-body">
							                    <button class="btn btn-info" id="synch_clients" data-xero_synch_area="true"><i class="fa fa-refresh"></i> Synchronize</button>
							                </div>
							            </div>
							        </div>
							        <div class="col-md-4 xero_sub">
							            <div class="panel_s">
							                <div class="panel-heading">Taxes<strong class="pull-right" data-toggle="tooltip" data-title="<?php echo _l('xero_download_helper_2'); ?>"><i class="text-warning fa-2x fa fa-download" ></i></strong><strong class="pull-right mright5" data-toggle="tooltip" data-title="<?php echo _l('xero_upload_helper_2'); ?>"><i class="text-info fa-2x fa fa-upload"></i></strong></div>
							                <div class="panel-body">
							                    <button class="btn btn-info" id="synch_taxes" data-xero_synch_area="true"><i class="fa fa-refresh"></i> Synchronize</button>
							                </div>
							            </div>
							        </div>
							        <div class="col-md-4 xero_sub">
							            <div class="panel_s">
							                <div class="panel-heading">Items<strong class="pull-right" data-toggle="tooltip" data-title="<?php echo _l('xero_upload_helper_2'); ?>"><i class="text-info fa-2x fa fa-upload"></i></strong></div>
							                <div class="panel-body">
							                    <button class="btn btn-info" id="synch_items" data-xero_synch_area="true"><i class="fa fa-refresh"></i> Synchronize</button>
							                </div>
							            </div>
							        </div>
							        <div class="col-md-4 xero_sub">
							            <div class="panel_s">
							                <div class="panel-heading">Invoices<strong class="pull-right" data-toggle="tooltip" data-title="<?php echo _l('xero_upload_helper_2'); ?>"><i class="text-info fa-2x fa fa-upload"></i></strong></div>
							                <div class="panel-body">
							                    <button class="btn btn-info" id="synch_invoices" data-xero_synch_area="true"><i class="fa fa-refresh"></i> Synchronize</button>
							                </div>
							            </div>
							        </div>
							        <div class="col-md-4 xero_sub">
							            <div class="panel_s">
							                <div class="panel-heading">Payments<strong class="pull-right" data-toggle="tooltip" data-title="<?php echo _l('xero_upload_helper_2'); ?>"><i class="text-info fa-2x fa fa-upload"></i></strong></div>
							                <div class="panel-body">
							                    <button class="btn btn-info" id="synch_payments" data-xero_synch_area="true"><i class="fa fa-refresh"></i> Synchronize</button>
							                </div>
							            </div>
							        </div>

							        <div class="col-md-4 xero_sub hide">
							            <div class="panel_s">
							                <div class="panel-heading">Expenses<strong class="pull-right" data-toggle="tooltip" data-title="<?php echo _l('xero_upload_helper_2'); ?>"><i class="text-info fa-2x fa fa-upload"></i></strong></div>
							                <div class="panel-body">
							                    <button class="btn btn-info" id="synch_expenses" data-xero_synch_area="true"><i class="fa fa-refresh"></i> Synchronize</button>
							                </div>
							            </div>
							        </div>
							    </div>
							<?php }?>
							</div>
		                </div>
			        </div>
			    </div>
			</div>
		</div>
	</div>
</div>
<?php init_tail();?>
</body>
</html>