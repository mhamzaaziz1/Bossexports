<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('daily_sales_report/report'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('daily_sales_report_add_new'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        
                        <div class="table-responsive">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('daily_sales_report_name'); ?></th>
                                        <th><?php echo _l('daily_sales_report_emails'); ?></th>
                                        <th><?php echo _l('daily_sales_report_active'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($reports as $report){ ?>
                                    <tr>
                                        <td><a href="<?php echo admin_url('daily_sales_report/report/' . $report['id']); ?>"><?php echo $report['name']; ?></a></td>
                                        <td><?php echo $report['emails']; ?></td>
                                        <td>
                                            <div class="onoffswitch">
                                                <input type="checkbox" disabled data-switch-url="<?php echo admin_url('daily_sales_report/change_active_status'); ?>" name="onoffswitch" class="onoffswitch-checkbox" id="c_<?php echo $report['id']; ?>" data-id="<?php echo $report['id']; ?>" <?php if($report['active'] == 1){echo 'checked';} ?>>
                                                <label class="onoffswitch-label" for="c_<?php echo $report['id']; ?>"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="<?php echo admin_url('daily_sales_report/report/' . $report['id']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil"></i></a>
                                            <a href="<?php echo admin_url('daily_sales_report/delete/' . $report['id']); ?>" class="btn btn-danger btn-icon _delete" onclick="return confirm('<?php echo _l('daily_sales_report_delete_confirm'); ?>');"><i class="fa fa-remove"></i></a>
                                            <a href="#" class="btn btn-success btn-icon" onclick="open_manual_send_modal(<?php echo $report['id']; ?>); return false;" title="<?php echo _l('daily_sales_report_send_manual'); ?>"><i class="fa fa-envelope"></i></a>
                                            <a href="#" class="btn btn-info btn-icon" onclick="open_opti_send_modal(<?php echo $report['id']; ?>); return false;" title="Optimetriks API Send (No Email)"><i class="fa fa-cloud-upload"></i></a>

                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('daily_sales_report_log'); ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <div class="table-responsive">
                            <table class="table dt-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('daily_sales_report_report_name'); ?></th>
                                        <th><?php echo _l('daily_sales_report_date_sent'); ?></th>
                                        <th><?php echo _l('daily_sales_report_email_to'); ?></th>
                                        <th><?php echo _l('daily_sales_report_records_count'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($logs as $log){ ?>
                                    <tr>
                                        <td><?php echo $log['report_name'] ? $log['report_name'] : 'N/A'; ?></td>
                                        <td><?php echo _dt($log['date_sent']); ?></td>
                                        <td><?php echo $log['email_to']; ?></td>
                                        <td><?php echo $log['records_count']; ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('daily_sales_report/download_log/' . $log['id']); ?>" class="btn btn-default btn-icon">
                                                <i class="fa fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="manual_send_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('daily_sales_report/preview_manual')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('daily_sales_report_send_manual'); ?></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="report_id" id="manual_report_id" value="">
                <div class="row">
                    <div class="col-md-6">
                        <?php echo render_date_input('report_start_date', 'daily_sales_report_start_date', _d(date('Y-m-d'))); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_date_input('report_end_date', 'daily_sales_report_end_date', _d(date('Y-m-d'))); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('daily_sales_report_preview'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<div class="modal fade" id="opti_send_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php echo form_open(admin_url('daily_sales_report/preview_manual')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Optimetriks Manual Send</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="report_id" id="opti_report_id" value="">
                <input type="hidden" name="is_opti_only" value="1">
                <div class="row">
                    <div class="col-md-6">
                        <?php // No 'id' attr here: render_date_input() uses it as the input name, which dropped the dates ?>
                        <?php echo render_date_input('report_start_date', 'daily_sales_report_start_date', _d(date('Y-m-d'))); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_date_input('report_end_date', 'daily_sales_report_end_date', _d(date('Y-m-d'))); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('daily_sales_report_preview'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php init_tail(); ?>
<script>
function open_manual_send_modal(id) {
    $('#manual_report_id').val(id);
    $('#manual_send_modal').modal('show');
}

function open_opti_send_modal(id) {
    $('#opti_report_id').val(id);
    $('#opti_send_modal').modal('show');
}

$(function(){
    $('#manual_send_modal form').on('submit', function(e){
        var start_date = $('#report_start_date').val();
        var end_date = $('#report_end_date').val();
        
        if(start_date != '' && end_date != '') {
            // Need to parse according to dateformat, but simplest is to assume they are valid and compare them if possible,
            // However, JS Date parsing of display formats can be tricky. 
            // We can just rely on the server side to enforce the 31 day limit, but we can also do a basic check.
            // Actually, server side validation is more reliable because of localized date formats.
        }
    });
});
</script>
</body>
</html>
