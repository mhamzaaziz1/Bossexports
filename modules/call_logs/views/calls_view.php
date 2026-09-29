<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                	<?php if (has_permission('call_logs','','create')): ?>
            		<div class="_buttons">
                        <a href="javascript:void(0);" onclick="new_call_log('call_logs/call'); return false;" class="btn btn-info pull-left display-block"><?php echo _l('new_call_log'); ?></a>
                        <a onclick="open_import_modal();" class="text-uppercase btn btn-success text-capitalize pull-right"><?php echo _l('import_staff'); ?></a>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="hr-panel-heading" />
                    <h4 class="mbot15">Call Logs Summary</h4>
	                <div class="row">
	                    <div class="col-md-2 col-xs-6 border-right">
	                        <h3 class="bold no-mtop"><?php echo get_counts('followup','yes'); ?></h3>
	                        <p style="color:#989898" class="font-medium no-mbot">Followup required</p>
	                    </div>
	                    <div class="col-md-2 col-xs-6 border-right">
	                        <h3 class="bold no-mtop"><?php echo get_counts('direction','inbound'); ?></h3>
	                        <p style="color:#989898" class="font-medium no-mbot">Inbound <i class="fa fa-arrow-down " title="Inbound" style="font-size: 20px; color: #ed143d"></i></p>
	                    </div>
	                    <div class="col-md-2 col-xs-6 border-right">
	                        <h3 class="bold no-mtop"><?php echo get_counts('direction','outbound'); ?></h3>
	                        <p style="color:#989898" class="font-medium no-mbot">Outbound <i class="fa fa-arrow-up " style="font-size: 20px; color: #84c529" title="Outbound"></i></p>
	                    </div>
                        <div class="col-md-2 col-xs-6 border-right">
                            <h3 class="bold no-mtop"><?php echo get_calls_total(); ?></h3>
                            <p style="color:#989898" class="font-medium no-mbot">Total Calls Today</p>
                        </div>
                        <div class="col-md-2 col-xs-6 border-right">
                            <h3 class="bold no-mtop"><?php echo get_calls_total(true); ?></h3>
                            <p style="color:#989898" class="font-medium no-mbot">Total Calls Yesterday</p>
                        </div>
	                </div>
                    <hr class="hr-panel-heading" />
                	<?php endif ?>
                    <?php render_datatable(array(
                        _l('id'),
	                     'Caller Name',
	                     'Caller Type',
	                     _l('call_direction'),
	                     _l('purpose_of_call'),
	                     _l('call_duration'),
	                     _l('followup'),
                         _l('call_for'),
	                     _l('call_owner'),
                         _l('invoice_no'),
	                     _l('call_created_at'),
                        ),'call-logs'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="_call_log"></div>
<div id="_call_log_edit"></div>
<?php init_tail(); ?>
<script>
    $(function(){
        initDataTable('.table-call-logs', window.location.href, [], [], [], [0, 'desc']);
        $('.table-call-logs').DataTable().on('draw', function() {
            var rows = $('.table-call-logs').find('tr');
        });
    });
</script>
</body>
</html>
