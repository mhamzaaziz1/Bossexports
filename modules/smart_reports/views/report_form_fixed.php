<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo isset($title) ? $title : _l('new_report'); ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <!-- AI Query Interface -->
                        <div class="panel-group" id="report-methods" role="tablist" aria-multiselectable="true">
                            <div class="panel panel-default">
                                <div class="panel-heading" role="tab" id="headingAI">
                                    <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse" data-parent="#report-methods" href="#collapseAI" aria-expanded="true" aria-controls="collapseAI">
                                            <i class="fa fa-magic"></i> <?php echo _l('ai_powered_reports'); ?>
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseAI" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingAI">
                                    <div class="panel-body">
                                        <p><?php echo _l('ai_reports_description'); ?></p>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="ai_query"><?php echo _l('describe_report_you_want'); ?></label>
                                                    <textarea id="ai_query" class="form-control" rows="3" placeholder="<?php echo _l('ai_query_placeholder'); ?>"></textarea>
                                                </div>
                                                
                                                <div id="ai-error-container" class="alert alert-danger" style="display:none;"></div>
                                                
                                                <button type="button" id="process-ai-query" class="btn btn-info">
                                                    <i class="fa fa-cogs"></i> <?php echo _l('generate_report'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="panel panel-default">
                                <div class="panel-heading" role="tab" id="headingForm">
                                    <h4 class="panel-title">
                                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#report-methods" href="#collapseForm" aria-expanded="false" aria-controls="collapseForm">
                                            <i class="fa fa-list-alt"></i> <?php echo _l('form_based_report'); ?>
                                        </a>
                                    </h4>
                                </div>
                                <div id="collapseForm" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingForm">
                                    <div class="panel-body">
                                        
                                        <?php echo form_open(admin_url('smart_reports/report/' . (isset($report) ? $report->id : '')), ['id' => 'report-form']); ?>
                                        
                                        <?php if(isset($ai_query)){ ?>
                                            <div class="alert alert-info">
                                                <strong><?php echo _l('ai_generated_report'); ?>:</strong> <?php echo html_escape($ai_query); ?>
                                                <input type="hidden" name="ai_query" value="<?php echo html_escape($ai_query); ?>">
                                                <?php if(isset($ai_generated_sql)){ ?>
                                                    <input type="hidden" name="ai_generated_sql" value="<?php echo html_escape($ai_generated_sql); ?>">
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="title"><?php echo _l('report_title'); ?> *</label>
                                                    <input type="text" id="title" name="title" class="form-control" value="<?php echo (isset($report) ? $report->title : (isset($_POST['title']) ? $_POST['title'] : '')); ?>" required>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="report_type"><?php echo _l('report_type'); ?> *</label>
                                                    <select id="report_type" name="report_type" class="form-control selectpicker" data-width="100%" required>
                                                        <option value=""><?php echo _l('select_report_type'); ?></option>
                                                        <?php 
                                                        $default_report_types = [
                                                            'sales' => _l('sales'),
                                                            'purchases' => _l('purchases'),
                                                            'inventory' => _l('inventory'),
                                                            'payments' => _l('payments'),
                                                            'leads' => _l('leads'),
                                                            'tasks' => _l('tasks'),
                                                            'custom_query' => _l('custom_query')
                                                        ];
                                                        $report_types = isset($report_types) ? $report_types : $default_report_types;
                                                        foreach($report_types as $key => $type){ 
                                                        ?>
                                                            <option value="<?php echo $key; ?>" <?php echo (isset($report) && $report->report_type == $key) || (isset($_POST['report_type']) && $_POST['report_type'] == $key) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="date_range"><?php echo _l('date_range'); ?></label>
                                                    <div class="input-group">
                                                        <input type="text" id="date_range_start" name="date_range_start" class="form-control datepicker" value="<?php echo (isset($report) ? _d($report->date_range_start) : ''); ?>" placeholder="<?php echo _l('from_date'); ?>">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-calendar calendar-icon"></i>
                                                        </div>
                                                        <input type="text" id="date_range_end" name="date_range_end" class="form-control datepicker" value="<?php echo (isset($report) ? _d($report->date_range_end) : ''); ?>" placeholder="<?php echo _l('to_date'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="group_by"><?php echo _l('group_by'); ?></label>
                                                    <select id="group_by" name="group_by" class="form-control selectpicker" data-width="100%">
                                                        <option value=""><?php echo _l('none'); ?></option>
                                                        <!-- Group by options will be populated via JavaScript based on report type -->
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="metric"><?php echo _l('metric'); ?></label>
                                                    <select id="metric" name="metric" class="form-control selectpicker" data-width="100%">
                                                        <option value=""><?php echo _l('none'); ?></option>
                                                        <!-- Metric options will be populated via JavaScript based on report type -->
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="sort_by"><?php echo _l('sort_by'); ?></label>
                                                    <select id="sort_by" name="sort_by" class="form-control selectpicker" data-width="100%">
                                                        <option value=""><?php echo _l('none'); ?></option>
                                                        <!-- Sort by options will be populated via JavaScript based on report type -->
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="limit_results"><?php echo _l('limit_results'); ?></label>
                                                    <input type="number" id="limit_results" name="limit_results" class="form-control" value="<?php echo (isset($report) ? $report->limit_results : '10'); ?>" min="1" max="1000">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="filter_by"><?php echo _l('filter_by'); ?></label>
                                                    <div id="filter_container">
                                                        <!-- Filter options will be populated via JavaScript based on report type -->
                                                        <div class="text-center mtop20" id="no_filters_message">
                                                            <p class="text-muted"><?php echo _l('select_report_type_for_filters'); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="output_format"><?php echo _l('output_format'); ?></label>
                                                    <div class="radio radio-primary radio-inline">
                                                        <input type="radio" id="output_format_table" name="output_format" value="table" <?php echo (!isset($report) || $report->output_format == 'table') ? 'checked' : ''; ?>>
                                                        <label for="output_format_table"><?php echo _l('table'); ?></label>
                                                    </div>
                                                    <div class="radio radio-primary radio-inline">
                                                        <input type="radio" id="output_format_bar" name="output_format" value="bar" <?php echo (isset($report) && $report->output_format == 'bar') ? 'checked' : ''; ?>>
                                                        <label for="output_format_bar"><?php echo _l('bar_chart'); ?></label>
                                                    </div>
                                                    <div class="radio radio-primary radio-inline">
                                                        <input type="radio" id="output_format_line" name="output_format" value="line" <?php echo (isset($report) && $report->output_format == 'line') ? 'checked' : ''; ?>>
                                                        <label for="output_format_line"><?php echo _l('line_chart'); ?></label>
                                                    </div>
                                                    <div class="radio radio-primary radio-inline">
                                                        <input type="radio" id="output_format_pie" name="output_format" value="pie" <?php echo (isset($report) && $report->output_format == 'pie') ? 'checked' : ''; ?>>
                                                        <label for="output_format_pie"><?php echo _l('pie_chart'); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                                <hr class="hr-panel-heading" />
                                                <button type="submit" class="btn btn-info pull-right"><?php echo _l('generate_report'); ?></button>
                                                <button type="button" class="btn btn-default pull-right mright5" onclick="window.location.href='<?php echo admin_url('smart_reports'); ?>'"><?php echo _l('cancel'); ?></button>
                                            </div>
                                        </div>
                                        
                                        <?php echo form_close(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Report Results Section -->
                        <div id="report-results" class="mtop30" style="display:none;">
                            <hr class="hr-panel-heading" />
                            <h4><?php echo _l('report_results'); ?></h4>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="btn-group pull-right">
                                        <button type="button" class="btn btn-default" id="export-csv">
                                            <i class="fa fa-file-excel-o"></i> <?php echo _l('export_csv'); ?>
                                        </button>
                                        <button type="button" class="btn btn-default" id="export-pdf">
                                            <i class="fa fa-file-pdf-o"></i> <?php echo _l('export_pdf'); ?>
                                        </button>
                                        <button type="button" class="btn btn-success" id="save-report">
                                            <i class="fa fa-floppy-o"></i> <?php echo _l('save_report'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mtop20">
                                <div class="col-md-12">
                                    <div id="chart-container" style="display:none; height:400px;"></div>
                                    <div id="table-container"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Save Report Modal -->
                        <div class="modal fade" id="save-report-modal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title"><?php echo _l('save_report'); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php echo form_open(admin_url('smart_reports/save_report'), ['id' => 'save-report-form']); ?>
                                        <input type="hidden" name="report_id" id="save_report_id" value="">
                                        
                                        <div class="form-group">
                                            <label for="name"><?php echo _l('report_name'); ?> *</label>
                                            <input type="text" id="name" name="name" class="form-control" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="description"><?php echo _l('description'); ?></label>
                                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                                        </div>
                                        
                                        <div class="checkbox checkbox-primary">
                                            <input type="checkbox" id="is_favorite" name="is_favorite">
                                            <label for="is_favorite"><?php echo _l('mark_as_favorite'); ?></label>
                                        </div>
                                        
                                        <?php echo form_close(); ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                        <button type="button" class="btn btn-info" id="save-report-btn"><?php echo _l('save'); ?></button>
                                    </div>
                                </div>
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
        // Initialize datepickers
        init_datepicker();
        
        // Report type change handler
        $('#report_type').on('change', function(){
            var reportType = $(this).val();
            populateFormOptions(reportType);
        });
        
        // If report type is already selected (edit mode), populate the form options
        if($('#report_type').val()) {
            populateFormOptions($('#report_type').val());
        }
        
        // Handle form submission
        $('#report-form').on('submit', function(e){
            e.preventDefault();
            generateReport();
        });
        
        // Handle export buttons
        $('#export-csv').on('click', function(){
            exportReport('csv');
        });
        
        $('#export-pdf').on('click', function(){
            exportReport('pdf');
        });
        
        // Handle save report button
        $('#save-report').on('click', function(){
            $('#save_report_id').val($('#report-form').data('report-id'));
            $('#name').val($('#title').val());
            $('#save-report-modal').modal('show');
        });
        
        // Handle save report form submission
        $('#save-report-btn').on('click', function(){
            if($('#name').val().trim() === '') {
                alert_float('warning', '<?php echo _l('report_name_required'); ?>');
                return;
            }
            
            $('#save-report-form').submit();
        });
        
        // If AI generated SQL is provided, auto-generate the report
        <?php if(isset($ai_generated_sql)){ ?>
            setTimeout(function(){
                generateReport();
            }, 1000);
        <?php } ?>
        
        // Handle AI query processing
        $('#process-ai-query').on('click', function(){
            var query = $('#ai_query').val().trim();
            
            if(query === '') {
                $('#ai-error-container').html('<?php echo _l('please_enter_query'); ?>').show();
                return;
            }
            
            // Hide any previous errors
            $('#ai-error-container').hide();
            
            // Show loading state
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('processing'); ?>');
            
            // Call the API
            $.ajax({
                url: admin_url + 'smart_reports/process_ai_query',
                type: 'POST',
                data: {
                    query: query
                },
                dataType: 'json',
                success: function(response) {
                    // Reset button
                    $btn.prop('disabled', false).html('<i class="fa fa-cogs"></i> <?php echo _l('generate_report'); ?>');
                    
                    if(response.success) {
                        // Redirect to report form with the generated SQL
                        var url = admin_url + 'smart_reports/report?ai_query=' + encodeURIComponent(query) + '&ai_generated_sql=' + encodeURIComponent(response.sql);
                        window.location.href = url;
                    } else {
                        // Show error
                        $('#ai-error-container').html(response.error || '<?php echo _l('error_processing_query'); ?>').show();
                    }
                },
                error: function() {
                    // Reset button
                    $btn.prop('disabled', false).html('<i class="fa fa-cogs"></i> <?php echo _l('generate_report'); ?>');
                    
                    // Show error
                    $('#ai-error-container').html('<?php echo _l('error_processing_query'); ?>').show();
                }
            });
        });
        
        // Allow pressing Enter in the textarea to submit
        $('#ai_query').on('keypress', function(e){
            if(e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                $('#process-ai-query').click();
            }
        });
    });
    
    // Function to populate form options based on report type
    function populateFormOptions(reportType) {
        // Clear existing options
        $('#group_by, #metric, #sort_by').empty();
        $('#filter_container').html('');
        
        // Add default empty option
        $('#group_by, #metric, #sort_by').append('<option value=""><?php echo _l('none'); ?></option>');
        
        // If no report type selected, show message and return
        if(!reportType) {
            $('#no_filters_message').show();
            $('.selectpicker').selectpicker('refresh');
            return;
        }
        
        // Hide no filters message
        $('#no_filters_message').hide();
        
        // Populate options based on report type
        switch(reportType) {
            case 'sales':
                // Group by options
                $('#group_by').append('<option value="customer"><?php echo _l('customer'); ?></option>');
                $('#group_by').append('<option value="staff"><?php echo _l('staff_member'); ?></option>');
                $('#group_by').append('<option value="date"><?php echo _l('date'); ?></option>');
                
                // Metric options
                $('#metric').append('<option value="count"><?php echo _l('count'); ?></option>');
                $('#metric').append('<option value="amount"><?php echo _l('amount'); ?></option>');
                
                // Sort by options
                $('#sort_by').append('<option value="date"><?php echo _l('date'); ?></option>');
                $('#sort_by').append('<option value="amount"><?php echo _l('amount'); ?></option>');
                
                // Filter options
                var filterHtml = '<div class="row">';
                filterHtml += '<div class="col-md-4"><div class="form-group"><label><?php echo _l('status'); ?></label>';
                filterHtml += '<select name="filter_by[status]" class="form-control selectpicker" data-width="100%">';
                filterHtml += '<option value=""><?php echo _l('all'); ?></option>';
                filterHtml += '<option value="1"><?php echo _l('invoice_status_unpaid'); ?></option>';
                filterHtml += '<option value="2"><?php echo _l('invoice_status_paid'); ?></option>';
                filterHtml += '<option value="3"><?php echo _l('invoice_status_partially_paid'); ?></option>';
                filterHtml += '<option value="4"><?php echo _l('invoice_status_overdue'); ?></option>';
                filterHtml += '</select></div></div>';
                
                filterHtml += '<div class="col-md-4"><div class="form-group"><label><?php echo _l('invoice_year'); ?></label>';
                filterHtml += '<select name="filter_by[year]" class="form-control selectpicker" data-width="100%">';
                filterHtml += '<option value=""><?php echo _l('all'); ?></option>';
                var currentYear = new Date().getFullYear();
                for(var i = currentYear; i >= currentYear - 5; i--) {
                    filterHtml += '<option value="' + i + '">' + i + '</option>';
                }
                filterHtml += '</select></div></div>';
                
                filterHtml += '</div>';
                $('#filter_container').html(filterHtml);
                break;
                
            case 'purchases':
                // Similar structure for purchases
                $('#group_by').append('<option value="vendor"><?php echo _l('vendor'); ?></option>');
                $('#group_by').append('<option value="staff"><?php echo _l('staff_member'); ?></option>');
                $('#group_by').append('<option value="date"><?php echo _l('date'); ?></option>');
                
                // Metric options
                $('#metric').append('<option value="count"><?php echo _l('count'); ?></option>');
                $('#metric').append('<option value="amount"><?php echo _l('amount'); ?></option>');
                
                // Sort by options
                $('#sort_by').append('<option value="date"><?php echo _l('date'); ?></option>');
                $('#sort_by').append('<option value="amount"><?php echo _l('amount'); ?></option>');
                break;
                
            case 'inventory':
                // Options for inventory
                $('#group_by').append('<option value="item_group"><?php echo _l('item_group'); ?></option>');
                
                // Metric options
                $('#metric').append('<option value="quantity"><?php echo _l('quantity'); ?></option>');
                $('#metric').append('<option value="value"><?php echo _l('value'); ?></option>');
                
                // Sort by options
                $('#sort_by').append('<option value="quantity"><?php echo _l('quantity'); ?></option>');
                $('#sort_by').append('<option value="value"><?php echo _l('value'); ?></option>');
                break;
                
            case 'payments':
                // Options for payments
                $('#group_by').append('<option value="payment_mode"><?php echo _l('payment_mode'); ?></option>');
                $('#group_by').append('<option value="customer"><?php echo _l('customer'); ?></option>');
                $('#group_by').append('<option value="date"><?php echo _l('date'); ?></option>');
                
                // Metric options
                $('#metric').append('<option value="count"><?php echo _l('count'); ?></option>');
                $('#metric').append('<option value="amount"><?php echo _l('amount'); ?></option>');
                
                // Sort by options
                $('#sort_by').append('<option value="date"><?php echo _l('date'); ?></option>');
                $('#sort_by').append('<option value="amount"><?php echo _l('amount'); ?></option>');
                break;
                
            case 'leads':
                // Options for leads
                $('#group_by').append('<option value="status"><?php echo _l('status'); ?></option>');
                $('#group_by').append('<option value="source"><?php echo _l('source'); ?></option>');
                $('#group_by').append('<option value="assigned"><?php echo _l('assigned'); ?></option>');
                
                // Metric options
                $('#metric').append('<option value="count"><?php echo _l('count'); ?></option>');
                
                // Sort by options
                $('#sort_by').append('<option value="date"><?php echo _l('date'); ?></option>');
                $('#sort_by').append('<option value="count"><?php echo _l('count'); ?></option>');
                break;
                
            case 'tasks':
                // Options for tasks
                $('#group_by').append('<option value="status"><?php echo _l('status'); ?></option>');
                $('#group_by').append('<option value="assignee"><?php echo _l('assignee'); ?></option>');
                
                // Metric options
                $('#metric').append('<option value="count"><?php echo _l('count'); ?></option>');
                $('#metric').append('<option value="duration"><?php echo _l('duration'); ?></option>');
                
                // Sort by options
                $('#sort_by').append('<option value="date"><?php echo _l('date'); ?></option>');
                $('#sort_by').append('<option value="count"><?php echo _l('count'); ?></option>');
                break;
                
            case 'custom_query':
                // For custom query, we don't need these options
                $('#group_by, #metric, #sort_by').prop('disabled', true);
                $('#filter_container').html('<div class="alert alert-info"><?php echo _l('custom_query_filters_not_applicable'); ?></div>');
                break;
        }
        
        // Refresh selectpickers
        $('.selectpicker').selectpicker('refresh');
    }
    
    // Function to generate report
    function generateReport() {
        var formData = $('#report-form').serialize();
        
        // If AI generated SQL is provided, add it to the form data
        <?php if(isset($ai_generated_sql)){ ?>
            formData += '&ai_generated_sql=<?php echo urlencode($ai_generated_sql); ?>';
        <?php } ?>
        
        $.ajax({
            url: admin_url + 'smart_reports/generate',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                // Show loading indicator
                $('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l('generating'); ?>');
            },
            success: function(response) {
                // Reset button
                $('button[type="submit"]').prop('disabled', false).html('<?php echo _l('generate_report'); ?>');
                
                if(response.success) {
                    // Store report ID for saving
                    $('#report-form').data('report-id', response.report_id);
                    
                    // Display results
                    displayReportResults(response.report_data);
                } else {
                    alert_float('danger', response.error || '<?php echo _l('error_generating_report'); ?>');
                }
            },
            error: function() {
                // Reset button
                $('button[type="submit"]').prop('disabled', false).html('<?php echo _l('generate_report'); ?>');
                alert_float('danger', '<?php echo _l('error_generating_report'); ?>');
            }
        });
    }
    
    // Function to display report results
    function displayReportResults(reportData) {
        // Show results section
        $('#report-results').show();
        
        // Get output format
        var outputFormat = $('input[name="output_format"]:checked').val();
        
        // Display data in table
        displayDataTable(reportData.data, reportData.columns);
        
        // If chart is selected, display chart
        if(outputFormat !== 'table') {
            displayChart(reportData.data, outputFormat);
        } else {
            $('#chart-container').hide();
        }
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#report-results').offset().top - 100
        }, 500);
    }
    
    // Function to display data in table
    function displayDataTable(data, columns) {
        var tableHtml = '<table class="table table-striped table-bordered">';
        
        // Add header row
        tableHtml += '<thead><tr>';
        columns.forEach(function(column) {
            tableHtml += '<th>' + column + '</th>';
        });
        tableHtml += '</tr></thead>';
        
        // Add data rows
        tableHtml += '<tbody>';
        data.forEach(function(row) {
            tableHtml += '<tr>';
            columns.forEach(function(column) {
                tableHtml += '<td>' + (row[column] !== null ? row[column] : '') + '</td>';
            });
            tableHtml += '</tr>';
        });
        tableHtml += '</tbody>';
        
        tableHtml += '</table>';
        
        $('#table-container').html(tableHtml);
    }
    
    // Function to display chart
    function displayChart(data, chartType) {
        $('#chart-container').show();
        
        // Prepare data for chart
        var chartData = prepareChartData(data);
        
        // Create chart
        switch(chartType) {
            case 'bar':
                createBarChart(chartData);
                break;
            case 'line':
                createLineChart(chartData);
                break;
            case 'pie':
                createPieChart(chartData);
                break;
        }
    }
    
    // Function to prepare data for chart
    function prepareChartData(data) {
        // This is a simplified implementation
        // In a real application, you would need to analyze the data structure
        // and prepare appropriate data for the chart
        
        var labels = [];
        var values = [];
        
        // Get the first and second columns for labels and values
        if(data.length > 0) {
            var keys = Object.keys(data[0]);
            
            data.forEach(function(row) {
                labels.push(row[keys[0]]);
                values.push(parseFloat(row[keys[1]]) || 0);
            });
        }
        
        return {
            labels: labels,
            values: values
        };
    }
    
    // Function to create bar chart
    function createBarChart(chartData) {
        // Implementation would depend on the charting library you're using
        // This is a placeholder
        console.log('Creating bar chart with data:', chartData);
    }
    
    // Function to create line chart
    function createLineChart(chartData) {
        // Implementation would depend on the charting library you're using
        // This is a placeholder
        console.log('Creating line chart with data:', chartData);
    }
    
    // Function to create pie chart
    function createPieChart(chartData) {
        // Implementation would depend on the charting library you're using
        // This is a placeholder
        console.log('Creating pie chart with data:', chartData);
    }
    
    // Function to export report
    function exportReport(format) {
        // Implementation would depend on your export functionality
        // This is a placeholder
        console.log('Exporting report as ' + format);
    }
</script>
</body>
</html>