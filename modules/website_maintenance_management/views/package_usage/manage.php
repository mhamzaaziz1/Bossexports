<?php

defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
							<?php
							if (staff_can('view', 'website_maintenance_packages'))
							{ ?>
                                <a href="<?php
								echo admin_url('website_maintenance_management/package_usage/export_csv'); ?>" class="btn btn-default">
                                    <i class="fa fa-download tw-mr-1"></i>
									<?php
									echo _l('export_excel'); ?>
                                </a>
								<?php
							} ?>
                        </div>
                        <hr class="hr-panel-heading"/>

                        <h4 class="tw-font-semibold tw-text-lg tw-mb-4">
                            <i class="fa fa-history tw-mr-2"></i>
							<?php
							echo _l('wmm_package_usage_history'); ?>
                        </h4>

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center">
                                        <h3 class="bold" id="total_records">-</h3>
                                        <p class="text-muted"><?php
											echo _l('wmm_total_usage_records'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center">
                                        <h3 class="bold" id="total_hours_consumed">-</h3>
                                        <p class="text-muted"><?php
											echo _l('wmm_total_hours_consumed'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center">
                                        <h3 class="bold text-info" id="monthly_records">-</h3>
                                        <p class="text-muted"><?php
											echo _l('wmm_this_month_usage'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel_s">
                                    <div class="panel-body text-center">
                                        <h3 class="bold text-success" id="monthly_hours">-</h3>
                                        <p class="text-muted"><?php
											echo _l('wmm_this_month_hours'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Usage Table -->
						<?php
						render_datatable([
							_l('id'),
							_l('wmm_package_name'),
							_l('wmm_customer'),
							_l('wmm_website'),
							_l('wmm_log_id'),
							_l('wmm_hours_consumed'),
							_l('wmm_consumed_at'),
							_l('wmm_consumed_by'),
						], 'package-usage'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
init_tail(); ?>

<script>
    var PackageUsageServerParams = {};

    $(function () {
        initDataTable('.table-package-usage', admin_url + '/website_maintenance_management/package_usage/table', [0], [0], PackageUsageServerParams, [6, 'desc']);
        load_summary();
    });

    function load_summary() {
        $.get(admin_url + 'website_maintenance_management/package_usage/get_summary', function (response) {
            if (response.success) {
                $('#total_records').text(response.summary.total_records);
                $('#total_hours_consumed').text(response.summary.total_hours_consumed + ' h');
                $('#monthly_records').text(response.summary.monthly_records);
                $('#monthly_hours').text(response.summary.monthly_hours + ' h');
            }
        }, 'json');
    }
</script>
