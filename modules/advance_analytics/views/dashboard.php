<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('advance_analytics_dashboard'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <p>Welcome to the Advance Analytics Dashboard. Select a category from the menu to view detailed analytics.</p>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('advance_analytics/sales'); ?>" class="text-dark">
                                    <div class="panel_s">
                                        <div class="panel-body text-center">
                                            <i class="fa fa-chart-bar fa-3x text-info"></i>
                                            <h4 class="bold"><?php echo _l('analytics_sales'); ?></h4>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('advance_analytics/finance'); ?>" class="text-dark">
                                    <div class="panel_s">
                                        <div class="panel-body text-center">
                                            <i class="fa fa-money-bill-alt fa-3x text-success"></i>
                                            <h4 class="bold"><?php echo _l('analytics_finance'); ?></h4>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('advance_analytics/customers'); ?>" class="text-dark">
                                    <div class="panel_s">
                                        <div class="panel-body text-center">
                                            <i class="fa fa-users fa-3x text-warning"></i>
                                            <h4 class="bold"><?php echo _l('analytics_customers'); ?></h4>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="<?php echo admin_url('advance_analytics/projects'); ?>" class="text-dark">
                                    <div class="panel_s">
                                        <div class="panel-body text-center">
                                            <i class="fa fa-project-diagram fa-3x text-danger"></i>
                                            <h4 class="bold"><?php echo _l('analytics_projects'); ?></h4>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
