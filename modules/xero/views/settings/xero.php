<?php defined('BASEPATH') or exit('No direct script access allowed');?>

<?php
$CI = &get_instance();
$xero_income_accounts_arr = array();
//$xero_asset_accounts_arr = array();
//$xero_payment_accounts_arr = array();

//Income
$xero_income_accounts = $CI->xero_model->get_xero_accounts("*", "REVENUE", '', true);
if (isset($xero_income_accounts->chart->REVENUE) && count($xero_income_accounts->chart->REVENUE) > 0) {
	foreach ($xero_income_accounts->chart->REVENUE as $key => $value) {
		$xero_income_accounts_arr[$value->Code] = $value->Name;
	}
}
?>

<?php if ((bool) get_option('xero_active')) {?>

<label class="control-label"><?php echo _l('xero'); ?> Connect</label>
<?php if (get_option('xero_access_token') && time() < get_option('xero_expiration_date')): ?>
<div class="row mbot10">
    <div class="col-md-8">
        <div class="alert alert-info"><i class="fa fa-check-square"></i> Sucessfully connected to <b><?php echo get_option('xero_tenant_name'); ?></b></div>
    </div>
    <div class="col-md-4">
        <button class="btn btn-default no-padding" id="xero_disconnect_btn"><img height="40" src="<?php echo module_dir_url('xero', 'assets/imgs/disconnect-white.svg'); ?>"></button>
    </div>
</div>
<div class="row mtop10 hide"><div class="col-md-12" id="process"><div class="progress progress-bar-mini"> <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div></div></div></div>

<?php else: ?>
    <div class="mbot10">
        <button class="btn btn-default no-padding" id="xero_connect_btn"><img height="40"  src="<?php echo module_dir_url('xero', 'assets/imgs/connect-white.svg'); ?>"></button>
    </div>
<?php endif;?>
<?php }?>

<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active">
        <a href="#general" aria-controls="general" role="tab" data-toggle="tab"><?php echo _l('xero_general'); ?></a>
    </li>
    <li role="presentation">
        <a href="#config" aria-controls="config" role="tab" data-toggle="tab"><?php echo _l('xero_config'); ?></a>
    </li>
    <li role="presentation">
        <a href="#reset" aria-controls="reset" role="tab" data-toggle="tab"><?php echo _l('xero_reset_sync'); ?></a>
    </li>
</ul>

<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="general">
        <h4 class="bold">
            <?php echo _l('xero_general'); ?>
        </h4>

        <?php echo render_yes_no_option('xero_active', 'xero_active'); ?>
        <hr />
        <?php echo render_input('settings[xero_client_id]', 'xero_client_id', get_option('xero_client_id')); ?>
        <?php echo render_input('settings[xero_client_secret]', 'xero_client_secret', get_option('xero_client_secret')); ?>
    </div>
    <div role="tabpanel" class="tab-pane" id="config">

        <?php if (get_option('cron_has_run_from_cli') != '1') {?>
            <div class="alert alert-danger">
                "Auto Sync" This feature requires a properly configured cron job. Before activating the feature, make sure that the <a href="<?php echo admin_url('settings?group=cronjob'); ?>">cron job</a> is configured as per the explanation in the documentation.
            </div>
        <?php }?>
        <div class="row mtop15">
            <div class="col-md-6">
                    <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('xero_manual_cron_sync_enabled_help'); ?>"></i>
                <?php echo render_yes_no_option('xero_manual_cron_sync_enabled', 'xero_manual_cron_sync_enabled'); ?>
            </div>
            <div class="col-md-6">
                    <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('xero_sync_payments_down_enabled_help'); ?>"></i>
                <?php echo render_yes_no_option('xero_sync_payments_down_enabled', 'xero_sync_payments_down_enabled'); ?>
            </div>
            <div class="col-md-6">
                    <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('xero_sync_invoice_ref_with_project_details_help'); ?>"></i>
                <?php echo render_yes_no_option('xero_sync_invoice_ref_with_project_details', 'xero_sync_invoice_ref_with_project_details'); ?>
            </div>
        </div>
<?php if (get_option('xero_manual_cron_sync_enabled') != '1') {?>
        <div class="row" id="auto_synch_area">
            <div class="col-md-12">
             <hr>
         </div>
            <div class="col-md-6">
        <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('xero_hour_of_day_perform_auto_operations_help'); ?>"></i>
        <?php echo render_input('settings[xero_auto_operations_hour]', 'hour_of_day_perform_auto_operations', get_option('xero_auto_operations_hour'), 'number', array('data-toggle' => 'tooltip', 'data-title' => _l('hour_of_day_perform_auto_operations_format'), 'max' => 23)); ?>
    </div>
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6">
                <?php echo render_yes_no_option('xero_auto_cron_sync_clients_enabled', 'xero_auto_cron_sync_clients_enabled'); ?>
            </div>

            <div class="col-md-6">
                <?php echo render_yes_no_option('xero_auto_cron_sync_taxes_enabled', 'xero_auto_cron_sync_taxes_enabled'); ?>
            </div>

            <div class="col-md-6">
                <?php echo render_yes_no_option('xero_auto_cron_sync_items_enabled', 'xero_auto_cron_sync_items_enabled'); ?>
            </div>

            <div class="col-md-6">
                <?php echo render_yes_no_option('xero_auto_cron_sync_invoices_enabled', 'xero_auto_cron_sync_invoices_enabled'); ?>
            </div>

            <div class="col-md-6">
                <?php echo render_yes_no_option('xero_auto_cron_sync_payments_enabled', 'xero_auto_cron_sync_payments_enabled'); ?>
            </div>
        </div>
    </div>
</div>
<?php }?>

        <hr>
        <div class="row">
            <div class="col-md-6">
                <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?php echo _l('xero_default_income_acc_help'); ?>"></i>
                <div class="form-group inc_acc">
                    <label class="control-label" for="parent_id"><?php echo _l('xero_default_income_acc'); ?></label>
                    <?php echo form_dropdown('settings[xero_default_income_account]', $xero_income_accounts_arr, get_option('xero_default_income_account'), array('id' => 'settings[xero_default_income_account]', 'class' => 'form-control selectpicker', 'data-live-search' => 'true', 'data-width' => '100%')); ?>
                </div>
            </div>
            </div>


</div>

    <div role="tabpanel" class="tab-pane" id="reset">
        <div class="alert alert-info"><?php echo _l('xero_reset_sync_help'); ?></div>
        <div class="row">
            <?php if (get_option('xero_access_token') && time() < get_option('xero_expiration_date')): ?>
            <div class="col-md-12 text-center">
                <button type="button" class="btn btn-warning" id="xero_reset_all_sync"><?php echo _l('xero_reset_sync'); ?></button>
            </div>
            <div id="xero_reset_progress_area" class="mtop10 col-md-12 hide"><div class="progress progress-bar-mini"> <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div></div></div>
            <?php else: ?>

              <div class="alert alert-warning text-center"><?php echo _l('requires_connection'); ?></div>

             <?php endif;?>
        </div>
    </div>
</div>