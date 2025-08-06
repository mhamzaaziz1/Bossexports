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
                        
                        <h4 class="no-margin"><?php echo _l('ai_query_logs'); ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <?php if(empty($ai_logs)){ ?>
                            <div class="alert alert-info">
                                <p class="text-center"><?php echo _l('no_ai_query_logs'); ?></p>
                            </div>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th><?php echo _l('query_text'); ?></th>
                                                    <th><?php echo _l('generated_sql'); ?></th>
                                                    <th><?php echo _l('created_by'); ?></th>
                                                    <th><?php echo _l('created_at'); ?></th>
                                                    <th><?php echo _l('options'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($ai_logs as $log){ ?>
                                                    <tr>
                                                        <td><?php echo $log['query_text']; ?></td>
                                                        <td>
                                                            <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#sql-modal-<?php echo $log['id']; ?>">
                                                                <i class="fa fa-code"></i> <?php echo _l('view_sql'); ?>
                                                            </button>
                                                            
                                                            <!-- SQL Modal -->
                                                            <div class="modal fade" id="sql-modal-<?php echo $log['id']; ?>" tabindex="-1" role="dialog">
                                                                <div class="modal-dialog modal-lg" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                                            <h4 class="modal-title"><?php echo _l('generated_sql'); ?></h4>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <pre><?php echo htmlspecialchars($log['generated_sql']); ?></pre>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo get_staff_full_name($log['created_by']); ?></td>
                                                        <td><?php echo _dt($log['created_at']); ?></td>
                                                        <td>
                                                            <?php if($log['report_id']){ ?>
                                                                <a href="<?php echo admin_url('smart_reports/report/' . $log['report_id']); ?>" class="btn btn-default btn-sm">
                                                                    <i class="fa fa-eye"></i> <?php echo _l('view_report'); ?>
                                                                </a>
                                                            <?php } else { ?>
                                                                <a href="<?php echo admin_url('smart_reports/report?ai_query=' . urlencode($log['query_text']) . '&ai_generated_sql=' . urlencode($log['generated_sql'])); ?>" class="btn btn-info btn-sm">
                                                                    <i class="fa fa-refresh"></i> <?php echo _l('regenerate_report'); ?>
                                                                </a>
                                                            <?php } ?>
                                                            
                                                            <?php if(has_permission('smart_reports', '', 'delete')){ ?>
                                                                <a href="<?php echo admin_url('smart_reports/delete_log/' . $log['id']); ?>" class="btn btn-danger btn-sm _delete">
                                                                    <i class="fa fa-remove"></i> <?php echo _l('delete'); ?>
                                                                </a>
                                                            <?php } ?>
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