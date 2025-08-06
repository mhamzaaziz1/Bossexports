<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if(has_permission('smart_reports', '', 'create')){ ?>
                        <div class="_buttons">
                            <a href="<?php echo admin_url('smart_reports/report'); ?>" class="btn btn-info pull-left display-block">
                                <i class="fa fa-plus"></i> <?php echo _l('new_report'); ?>
                            </a>
                            <a href="<?php echo admin_url('smart_reports/saved_reports'); ?>" class="btn btn-default pull-left display-block mright5">
                                <i class="fa fa-star"></i> <?php echo _l('saved_reports'); ?>
                            </a>
                            <a href="<?php echo admin_url('smart_reports/ai_logs'); ?>" class="btn btn-default pull-left display-block mright5">
                                <i class="fa fa-history"></i> <?php echo _l('ai_query_logs'); ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php } ?>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="no-margin"><?php echo _l('smart_reports'); ?></h4>
                                <hr class="hr-panel-heading" />
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><i class="fa fa-magic"></i> <?php echo _l('ai_powered_reports'); ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <p><?php echo _l('ai_reports_description'); ?></p>
                                        <form id="ai-query-form" action="<?php echo admin_url('smart_reports/process_ai_query'); ?>" method="post">
                                            <div class="form-group">
                                                <label for="ai_query"><?php echo _l('describe_report_you_want'); ?></label>
                                                <textarea id="ai_query" name="ai_query" class="form-control" rows="3" placeholder="<?php echo _l('ai_query_placeholder'); ?>"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-magic"></i> <?php echo _l('generate_report'); ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h4><?php echo _l('recent_reports'); ?></h4>
                                <hr class="hr-panel-heading" />
                                <?php render_datatable(array(
                                    _l('report_title'),
                                    _l('report_type'),
                                    _l('date_range'),
                                    _l('created_by'),
                                    _l('created_at'),
                                    _l('options'),
                                ), 'smart-reports'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function(){
        initDataTable('.table-smart-reports', window.location.href, [5], [5]);
        
        // Handle AI query form submission
        $('#ai-query-form').on('submit', function(e){
            e.preventDefault();
            var query = $('#ai_query').val();
            
            if(query.trim() === '') {
                alert_float('warning', '<?php echo _l('please_enter_query'); ?>');
                return false;
            }
            
            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            
            $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('processing'); ?>').prop('disabled', true);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: {
                    query: query
                },
                dataType: 'json',
                success: function(response){
                    $submitBtn.html(originalText).prop('disabled', false);
                    
                    if(response.success) {
                        // Redirect to report form with pre-filled data
                        var url = '<?php echo admin_url('smart_reports/report'); ?>';
                        var form = $('<form action="' + url + '" method="post"></form>');
                        
                        form.append('<input type="hidden" name="title" value="AI Generated Report">');
                        form.append('<input type="hidden" name="report_type" value="custom_query">');
                        form.append('<input type="hidden" name="ai_query" value="' + query + '">');
                        form.append('<input type="hidden" name="ai_generated_sql" value="' + response.sql + '">');
                        
                        $('body').append(form);
                        form.submit();
                    } else {
                        alert_float('danger', response.error || '<?php echo _l('error_processing_query'); ?>');
                    }
                },
                error: function(){
                    $submitBtn.html(originalText).prop('disabled', false);
                    alert_float('danger', '<?php echo _l('error_processing_query'); ?>');
                }
            });
        });
    });
</script>
</body>
</html>