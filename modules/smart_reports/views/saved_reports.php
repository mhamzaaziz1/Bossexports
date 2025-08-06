<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('smart_reports'); ?>" class="btn btn-default pull-left display-block">
                                <i class="fa fa-arrow-left"></i> <?php echo _l('back_to_reports'); ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        
                        <h4 class="no-margin"><?php echo _l('saved_reports'); ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <?php if(empty($saved_reports)){ ?>
                            <div class="alert alert-info">
                                <p class="text-center"><?php echo _l('no_saved_reports'); ?></p>
                            </div>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th><?php echo _l('report_name'); ?></th>
                                                    <th><?php echo _l('report_title'); ?></th>
                                                    <th><?php echo _l('description'); ?></th>
                                                    <th><?php echo _l('created_by'); ?></th>
                                                    <th><?php echo _l('created_at'); ?></th>
                                                    <th><?php echo _l('options'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($saved_reports as $report){ ?>
                                                    <tr>
                                                        <td>
                                                            <?php if($report['is_favorite']){ ?>
                                                                <i class="fa fa-star text-warning" title="<?php echo _l('favorite'); ?>"></i>
                                                            <?php } ?>
                                                            <?php echo $report['name']; ?>
                                                        </td>
                                                        <td><?php echo $report['report_title']; ?></td>
                                                        <td><?php echo $report['description'] ? $report['description'] : '-'; ?></td>
                                                        <td><?php echo get_staff_full_name($report['created_by']); ?></td>
                                                        <td><?php echo _dt($report['created_at']); ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="<?php echo admin_url('smart_reports/report/' . $report['report_id']); ?>" class="btn btn-default btn-sm">
                                                                    <i class="fa fa-eye"></i> <?php echo _l('view'); ?>
                                                                </a>
                                                                <?php if(has_permission('smart_reports', '', 'delete')){ ?>
                                                                    <a href="<?php echo admin_url('smart_reports/delete_saved/' . $report['id']); ?>" class="btn btn-danger btn-sm _delete">
                                                                        <i class="fa fa-remove"></i> <?php echo _l('delete'); ?>
                                                                    </a>
                                                                <?php } ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>