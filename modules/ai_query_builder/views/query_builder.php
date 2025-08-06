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

                        <?php if (!empty($saved_queries)): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel-heading">
                                    <h4><?php echo _l('saved_queries'); ?></h4>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th><?php echo _l('name'); ?></th>
                                                <th><?php echo _l('query'); ?></th>
                                                <th><?php echo _l('date'); ?></th>
                                                <th><?php echo _l('options'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($saved_queries as $saved_query): ?>
                                            <tr>
                                                <td><?php echo $saved_query['name']; ?></td>
                                                <td><?php echo $saved_query['query']; ?></td>
                                                <td><?php echo _dt($saved_query['date_created']); ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-xs load-query" data-id="<?php echo $saved_query['id']; ?>">
                                                        <i class="fa fa-play"></i> <?php echo _l('load'); ?>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <hr class="hr-panel-heading" />
                            </div>
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
                                    <button id="save-query" class="btn btn-info" style="display: none;">
                                        <i class="fa fa-save"></i> <?php echo _l('save_query'); ?>
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

<!-- Save Query Modal -->
<div class="modal fade" id="saveQueryModal" tabindex="-1" role="dialog" aria-labelledby="saveQueryModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="saveQueryModalLabel"><?php echo _l('save_query'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="query-name"><?php echo _l('query_name'); ?></label>
                    <input type="text" class="form-control" id="query-name" placeholder="<?php echo _l('enter_query_name'); ?>">
                </div>
                <input type="hidden" id="query-log-id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary" id="save-query-confirm"><?php echo _l('save'); ?></button>
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
        $('#save-query').hide();

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

                    // Store the log ID for saving
                    $('#query-log-id').val(response.log_id);

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

                        // Show export and save buttons
                        $('#export-csv').show();
                        $('#save-query').show();
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

                    // Hide save button on error
                    $('#save-query').hide();
                }
            },
            error: function(xhr, status, error) {
                $('#loading-container').hide();
                $('#error-container').text("<?php echo _l('ajax_error'); ?>: " + error);
                $('#error-container').show();
                $('#save-query').hide();
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

    // Save Query button click
    $('#save-query').on('click', function() {
        // Clear the input field
        $('#query-name').val('');

        // Show the modal
        $('#saveQueryModal').modal('show');
    });

    // Save Query Confirm button click
    $('#save-query-confirm').on('click', function() {
        var name = $('#query-name').val().trim();
        var id = $('#query-log-id').val();

        if (!name) {
            alert_float('warning', "<?php echo _l('query_name_required'); ?>");
            return;
        }

        // Send AJAX request
        $.ajax({
            url: admin_url + 'ai_query_builder/save_query',
            type: 'POST',
            data: {
                id: id,
                name: name
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Hide the modal
                    $('#saveQueryModal').modal('hide');

                    // Show success message
                    alert_float('success', response.message);

                    // Reload the page to show the saved query in the list
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert_float('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                alert_float('danger', "<?php echo _l('ajax_error'); ?>: " + error);
            }
        });
    });

    // Load Query button click
    $('.load-query').on('click', function() {
        var id = $(this).data('id');

        // Send AJAX request
        $.ajax({
            url: admin_url + 'ai_query_builder/load_query',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Set the query in the textarea
                    $('#query').val(response.query);

                    // Run the query
                    $('#run-query').click();
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
