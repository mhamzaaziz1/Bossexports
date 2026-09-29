<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('daily_sales_report_global_settings'); ?></h4>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-12">
                                <h5><strong>Cron Job Setup</strong></h5>
                                <p>To automatically send the daily sales reports, you need to configure a cron job on your server (e.g. via cPanel, Plesk, or crontab). This cron job should call the specific endpoint for this module.</p>
                                
                                <div class="alert alert-info">
                                    <strong>Cron Job URL:</strong><br />
                                    <?php 
                                        $cron_key = defined('APP_CRON_KEY') ? APP_CRON_KEY : '';
                                        $cron_url = site_url('daily_sales_report/cron/index/' . $cron_key);
                                    ?>
                                    <code style="word-break: break-all;"><?php echo 'wget -q -O- ' . $cron_url; ?></code>
                                </div>
                                
                                <p>You can configure this cron job to run at your desired interval (for example, once a day at 11:00 PM, or every 12 hours). Every time the cron job executes, the module will generate and send the active reports to their respective email lists.</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
