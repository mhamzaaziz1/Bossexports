<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('ai_query_builder'); ?></h4>
                        <hr class="hr-panel-heading" />
                        
                        <?php if (empty($settings->openai_api_key)): ?>
                        <div class="alert alert-warning">
                            <?php echo _l('openai_api_key_missing_warning'); ?>
                            <?php if (is_admin()): ?>
                            <a href="<?php echo admin_url('ai_query_builder/settings'); ?>" class="alert-link">
                                <?php echo _l('go_to_settings'); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="query"><?php echo _l('enter_your_query'); ?></label>
                                    <textarea id="query" class="form-control" rows="3" placeholder="<?php echo _l('query_placeholder'); ?>"></textarea>
                                </div>
                                <div class="form-group">
                                    <button id="run-query" class="btn btn-primary">
                                        <i class="fa fa-play"></i> <?php echo _l('run_query'); ?>
                                    </button>
                                    <button id="export-csv" class="btn btn-success" style="display: none;">
                                        <i class="fa fa-download"></i> <?php echo _l('export_csv'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row" id="results-container" style="display: none;">
                            <div class="col-md-12">
                                <hr class="hr-panel-heading" />
                                <h4><?php echo _l('generated_sql'); ?></h4>
                                <div class="form-group">
                                    <pre id="sql-output" class="pre-scrollable" style="max-height: 150px;"></pre>
                                </div>
                                
                                <div id="query-stats" class="text-muted" style="margin-bottom: 15px;"></div>
                                
                                <h4><?php echo _l('query_results'); ?></h4>
                                <div class="table-responsive">
                                    <table id="results-table" class="table table-striped table-bordered">
                                        <thead id="results-header"></thead>
                                        <tbody id="results-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div id="loading-container" style="display: none; text-align: center; margin-top: 20px;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only"><?php echo _l('loading'); ?></span>
                            </div>
                            <p><?php echo _l('processing_query'); ?></p>
                        </div>
                        
                        <div id="error-container" class="alert alert-danger" style="display: none; margin-top: 20px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(function() {
    // Run query button click
    $('#run-query').on('click', function() {
        var query = $('#query').val().trim();
        
        if (!query) {
            alert_float('warning', "<?php echo _l('query_empty'); ?>");
            return;
        }
        
        // Show loading
        $('#loading-container').show();
        $('#results-container').hide();
        $('#error-container').hide();
        $('#export-csv').hide();
        
        // Send AJAX request
        $.ajax({
            url: admin_url + 'ai_query_builder/process_query',
            type: 'POST',
            data: {
                query: query
            },
            dataType: 'json',
            success: function(response) {
                $('#loading-container').hide();
                
                if (response.success) {
                    // Display SQL
                    $('#sql-output').text(response.sql);
                    
                    // Display stats
                    $('#query-stats').html(
                        "<?php echo _l('execution_time'); ?>: " + response.execution_time + "s | " +
                        "<?php echo _l('rows_returned'); ?>: " + response.row_count
                    );
                    
                    // Clear previous results
                    $('#results-header').empty();
                    $('#results-body').empty();
                    
                    if (response.data.length > 0) {
                        // Create table header
                        var headerRow = $('<tr>');
                        $.each(response.data[0], function(key, value) {
                            headerRow.append($('<th>').text(key));
                        });
                        $('#results-header').append(headerRow);
                        
                        // Create table rows
                        $.each(response.data, function(i, row) {
                            var tableRow = $('<tr>');
                            $.each(row, function(key, value) {
                                tableRow.append($('<td>').text(value !== null ? value : 'NULL'));
                            });
                            $('#results-body').append(tableRow);
                        });
                        
                        // Show export button
                        $('#export-csv').show();
                    } else {
                        $('#results-body').html('<tr><td colspan="100%" class="text-center"><?php echo _l('no_results'); ?></td></tr>');
                    }
                    
                    // Show results
                    $('#results-container').show();
                } else {
                    // Show error
                    $('#error-container').text(response.message);
                    $('#error-container').show();
                    
                    // If SQL was generated but invalid, show it
                    if (response.sql) {
                        $('#sql-output').text(response.sql);
                        $('#results-container').show();
                    }
                }
            },
            error: function(xhr, status, error) {
                $('#loading-container').hide();
                $('#error-container').text("<?php echo _l('ajax_error'); ?>: " + error);
                $('#error-container').show();
            }
        });
    });
    
    // Export CSV button click
    $('#export-csv').on('click', function() {
        var sql = $('#sql-output').text().trim();
        
        if (!sql) {
            return;
        }
        
        // Send AJAX request
        $.ajax({
            url: admin_url + 'ai_query_builder/export_csv',
            type: 'POST',
            data: {
                sql: sql
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Create download link
                    var link = document.createElement('a');
                    link.href = 'data:text/csv;base64,' + response.csv_content;
                    link.download = response.filename;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                alert_float('danger', "<?php echo _l('ajax_error'); ?>: " + error);
            }
        });
    });
});
</script>